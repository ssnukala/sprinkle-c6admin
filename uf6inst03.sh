#!/usr/bin/env bash
set -e

echo "==========================================="
echo " 🧁 UserFrosting 6 Local Setup (Docker) "
echo "==========================================="

read -p "Enter the directory where UF6 should be installed (default: ./uf6): " PACKAGE_DIR
PACKAGE_DIR=${PACKAGE_DIR:-./uf6}

SPRINKLES=(
  "sprinkle-c6admin"
  "sprinkle-crud6"
)

SPRINKLE_PATH=$(pwd)/sprinkles
COMPOSER_BASE="docker run --rm -it -v $(pwd):/app -w /app composer"

# -----------------------------------------------------------------------------
# Generic patch-after helper
# -----------------------------------------------------------------------------
patch_after() {
  local file="$1" match="$2" insert="$3"
  [ ! -f "$file" ] && echo "⚠️  File not found: $file" && return 1
  grep -qF "$insert" "$file" && echo "↩️  Already patched: $file (skip)" && return 0
  local tmp; tmp="$(mktemp "${TMPDIR:-/tmp}/patch.XXXXXX")"
  local insert_tmp; insert_tmp="$(mktemp "${TMPDIR:-/tmp}/insert.XXXXXX")"
  printf "%s\n" "$insert" > "$insert_tmp"
  awk -v m="$match" -v insert_file="$insert_tmp" '
    { print $0 }
    index($0,m)>0 {
      while ((getline line < insert_file) > 0) print line
      close(insert_file)
    }
  ' "$file" > "$tmp" && mv "$tmp" "$file"
  rm -f "$insert_tmp"
  echo "✅ Patched: $file (after: $match)"
}

# -----------------------------------------------------------------------------
# Router-specific patch (inject createC6AdminRoutes block before routes array close)
# -----------------------------------------------------------------------------
patch_router_c6admin() {
  local file="app/assets/router/index.ts"
  [ ! -f "$file" ] && echo "⚠️  Router file missing" && return 1
  grep -q "createC6AdminRoutes" "$file" && echo "↩️  Router already has createC6AdminRoutes (skip)" && return 0

  echo "🔧 Patching router imports for C6Admin..."
  patch_after "$file" \
    "import AdminRoutes from '@userfrosting/sprinkle-admin/routes'" \
    "import { createC6AdminRoutes } from '@ssnukala/sprinkle-c6admin/routes', import LayoutDashboard from '../layouts/LayoutDashboard.vue'"

  echo "🔧 Injecting dynamic C6Admin routes..."
  local tmp; tmp="$(mktemp "${TMPDIR:-/tmp}/router.XXXXXX")"
  awk '
    /createRouter\({/ { inRouter=1 }
    inRouter && /\]\)/ {
      # Before closing routes array
      print "        // C6Admin routes with their own layout"
      print "        ...createC6AdminRoutes({ layoutComponent: LayoutDashboard })"
      inRouter=0
    }
    { print }
  ' "$file" > "$tmp" && mv "$tmp" "$file"

  echo "✅ Router dynamic C6Admin routes added"
}

# -----------------------------------------------------------------------------
# Vite optimizeDeps patch (add limax + lodash.deburr)
# -----------------------------------------------------------------------------
patch_vite_optimize_deps() {
  local file="vite.config.ts"
  [ ! -f "$file" ] && echo "⚠️  vite.config.ts not found, skipping optimizeDeps patch" && return 0

  if grep -q "'limax'" "$file" && grep -q "'lodash.deburr'" "$file"; then
    echo "↩️  vite.config.ts already includes limax & lodash.deburr (skip)"
    return 0
  fi

  echo "🔧 Patching vite.config.ts optimizeDeps include for limax + lodash.deburr..."

  local tmp; tmp="$(mktemp "${TMPDIR:-/tmp}/vite.XXXXXX")"
  awk '
    /optimizeDeps:[[:space:]]*\{/ { inOpt=1 }
    inOpt && /include:[[:space:]]*\[/ {
      # Capture include array (single or multi-line)
      if ($0 ~ /\]/) {
        sub(/\]/, ", '\''limax'\'', '\''lodash.deburr'\'']")
        print; next
      } else {
        print; inArray=1; next
      }
    }
    inArray {
      if ($0 ~ /\]/) {
        print "            '\''limax'\'',"
        print "            '\''lodash.deburr'\''"
        print; inArray=0; next
      }
      print; next
    }
    { print }
  ' "$file" > "$tmp" && mv "$tmp" "$file"

  echo "✅ vite.config.ts optimizeDeps updated"
}

# -----------------------------------------------------------------------------
# MyApp.php patch (add C6Admin::class if missing)
# -----------------------------------------------------------------------------
patch_myapp() {
  local file="app/src/MyApp.php"
  [ ! -f "$file" ] && echo "⚠️  MyApp.php missing" && return 1

  grep -q "use UserFrosting\\Sprinkle\\C6Admin\\C6Admin;" "$file" || \
    patch_after "$file" "use UserFrosting\\Sprinkle\\Core\\Core;" "use UserFrosting\\Sprinkle\\C6Admin\\C6Admin;"

  grep -q "C6Admin::class" "$file" || \
    patch_after "$file" "Admin::class," "            C6Admin::class,"
}

# -----------------------------------------------------------------------------
# main.ts patch (register C6AdminSprinkle)
# -----------------------------------------------------------------------------
patch_main_ts() {
  local file="app/assets/main.ts"
  [ ! -f "$file" ] && echo "⚠️  main.ts missing" && return 1
  grep -q "C6AdminSprinkle" "$file" && echo "↩️  main.ts already patched (skip)" && return 0

  patch_after "$file" \
    "import AdminSprinkle from '@userfrosting/sprinkle-admin'" \
    "import C6AdminSprinkle from '@ssnukala/sprinkle-c6admin'"

  patch_after "$file" \
    "app.use(AdminSprinkle)" \
    "app.use(C6AdminSprinkle)"
}

# -----------------------------------------------------------------------------
# package.json patch for local sprinkle dependencies
# -----------------------------------------------------------------------------
patch_package_json() {
  local file="package.json"
  [ ! -f "$file" ] && echo "⚠️  package.json missing" && return 1

  for SPRINKLE in "${SPRINKLES[@]}"; do
    local dep="@ssnukala/${SPRINKLE}"
    grep -q "\"${dep}\"" "$file" && echo "↩️  ${dep} already in package.json (skip)" && continue

    echo "🔧 Adding ${dep} to package.json dependencies..."
    patch_after "$file" \
      "\"@userfrosting/sprinkle-admin\":" \
      "    \"${dep}\": \"file:./ssnukala/${SPRINKLE}\","
  done
}

# -----------------------------------------------------------------------------
# Composer config (repositories + require)
# -----------------------------------------------------------------------------
configure_composer() {
  local COMPOSER="docker run --rm -it -v $(pwd):/app -v ${SPRINKLE_PATH}:${SPRINKLE_PATH} -w /app composer"
  mkdir -p ssnukala

  for SPRINKLE in "${SPRINKLES[@]}"; do
    if [ ! -d "ssnukala/${SPRINKLE}" ]; then
      echo "📦 Copying ${SPRINKLE} → ssnukala/${SPRINKLE}"
      cp -R "${SPRINKLE_PATH}/${SPRINKLE}" "ssnukala/${SPRINKLE}"
    else
      echo "↩️  ssnukala/${SPRINKLE} already exists (skip copy)"
    fi

    local repoKey
    repoKey=$(echo "${SPRINKLE}" | tr '[:upper:]' '[:lower:]')

    echo "🛠  Composer repository: ${repoKey}"
    $COMPOSER config "repositories.${repoKey}" path "ssnukala/${SPRINKLE}"

    echo "➕ Composer require: ssnukala/${repoKey}:@dev"
    $COMPOSER require "ssnukala/${repoKey}":@dev --no-update
  done

  echo "⚙️  Setting composer stability flags..."
  $COMPOSER config minimum-stability dev
  $COMPOSER config prefer-stable true
}

# -----------------------------------------------------------------------------
# 1. Create project
# -----------------------------------------------------------------------------
echo "🚀 Creating UF6 project in ${PACKAGE_DIR}..."
$COMPOSER_BASE create-project userfrosting/userfrosting "${PACKAGE_DIR}" "^6.0-beta" \
  --no-scripts --no-install --ignore-platform-reqs

cd "$PACKAGE_DIR" || { echo "❌ Folder missing"; exit 1; }
echo "📁 Now inside: $(pwd)"

# -----------------------------------------------------------------------------
# 2. Patch core files
# -----------------------------------------------------------------------------
echo "🔧 Applying core patches..."
patch_myapp
patch_router_c6admin
patch_main_ts
patch_package_json
patch_vite_optimize_deps

# -----------------------------------------------------------------------------
# 3. Composer sprinkle linking
# -----------------------------------------------------------------------------
configure_composer

echo ""
echo "==========================================="
echo "🎉 UserFrosting 6 setup complete!"
echo "Linked sprinkles:"
for SPRINKLE in "${SPRINKLES[@]}"; do
  echo "  - $SPRINKLE → ssnukala/${SPRINKLE}"
done
echo "You can now run:"
echo ""
echo "  docker run --rm -it -v $(pwd):/app -w /app composer install"
echo "  npm install"
echo "  npm run vite:dev"
echo ""
echo "Project located at: $(pwd)"
echo "==========================================="