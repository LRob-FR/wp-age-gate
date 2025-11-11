#!/bin/bash

# LRob Age Gate - Release Builder
# Generates translation files and creates distributable zip archive

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Paths
SCRIPT_DIR="$(dirname "$(readlink -f "${BASH_SOURCE[0]}")")"
PARENT_DIR="$(dirname "$SCRIPT_DIR")"
PLUGIN_DIR_NAME="$(basename "$SCRIPT_DIR")"
PLUGIN_SLUG="lrob-age-gate"
PLUGIN_FILE="${SCRIPT_DIR}/${PLUGIN_SLUG}.php"
LANGUAGES_DIR="${SCRIPT_DIR}/languages"
RELEASES_DIR="${PARENT_DIR}/releases"

print_status() {
    echo -e "${BLUE}==>${NC} $1"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}!${NC} $1"
}

command_exists() {
    command -v "$1" >/dev/null 2>&1
}

check_dependencies() {
    print_status "Checking dependencies..."
    local missing=0

    if ! command_exists php; then
        print_error "PHP not installed"
        echo "  Install: sudo dnf install php-cli"
        missing=1
    else
        print_success "PHP $(php -r 'echo PHP_VERSION;') found"
    fi

    if ! command_exists wp; then
        print_error "WP-CLI not installed"
        echo "  Install: sudo dnf install wp-cli"
        missing=1
    else
        print_success "WP-CLI $(wp --version | grep -oP '\d+\.\d+\.\d+') found"
    fi

    if ! command_exists msgfmt; then
        print_error "msgfmt (gettext) not installed"
        echo "  Install: sudo dnf install gettext"
        missing=1
    else
        print_success "msgfmt $(msgfmt --version | head -1 | grep -oP '\d+\.\d+\.\d+') found"
    fi

    if ! command_exists zip; then
        print_error "zip not installed"
        echo "  Install: sudo dnf install zip"
        missing=1
    else
        print_success "zip found"
    fi

    if [ $missing -eq 1 ]; then
        print_error "Missing dependencies. Install them and try again."
        exit 1
    fi

    echo ""
}

get_version() {
    if [ ! -f "$PLUGIN_FILE" ]; then
        print_error "Plugin file not found: $PLUGIN_FILE"
        exit 1
    fi
    grep -oP "Version:\s*\K[\d.]+" "$PLUGIN_FILE"
}

generate_translation_source() {
    print_status "Generating translation source from JSON presets..."

    local generator="${SCRIPT_DIR}/dev/generate-translations.php"

    if [ ! -f "$generator" ]; then
        print_error "Generator script not found: $generator"
        exit 1
    fi

    php "$generator"

    if [ $? -eq 0 ]; then
        print_success "Translation source generated"
    else
        print_error "Failed to generate translation source"
        exit 1
    fi
}

generate_pot() {
    print_status "Generating translation template (.pot)..."

    mkdir -p "$LANGUAGES_DIR"

    # Include dev/translations-source.php for scanning
    wp i18n make-pot "$SCRIPT_DIR" "$LANGUAGES_DIR/${PLUGIN_SLUG}.pot" \
        --domain="$PLUGIN_SLUG" \
        --package-name="LRob Age Gate" \
        --skip-js \
        --include="dev/translations-source.php"

    if [ $? -eq 0 ]; then
        print_success "POT file generated: ${LANGUAGES_DIR}/${PLUGIN_SLUG}.pot"

        # Strip source reference comments
        sed -i '/^#: /d' "$LANGUAGES_DIR/${PLUGIN_SLUG}.pot"
        print_success "Source references removed"
    else
        print_error "Failed to generate POT file"
        exit 1
    fi
}

compile_translations() {
    print_status "Compiling translations (.po → .mo)..."

    local compiled=0

    shopt -s nullglob
    local po_files=("$LANGUAGES_DIR"/*.po)
    shopt -u nullglob

    if [ ${#po_files[@]} -eq 0 ]; then
        print_warning "No .po files found"
        return 0
    fi

    for po_file in "${po_files[@]}"; do
        mo_file="${po_file%.po}.mo"

        if msgfmt -o "$mo_file" "$po_file" 2>/dev/null; then
            print_success "Compiled: $(basename "$mo_file")"
            compiled=$((compiled + 1))
        else
            print_error "Failed to compile: $(basename "$po_file")"
        fi
    done

    if [ $compiled -gt 0 ]; then
        print_success "Compiled $compiled translation file(s)"
    fi
}

create_archive() {
    local version=$1
    local archive_name="${PLUGIN_SLUG}-${version}.zip"
    local archive_path="${RELEASES_DIR}/${archive_name}"

    print_status "Creating release archive..."

    mkdir -p "$RELEASES_DIR"

    [ -f "$archive_path" ] && rm "$archive_path"

    local temp_list=$(mktemp)

    (cd "$PARENT_DIR" && find "$PLUGIN_DIR_NAME" -type f \
        ! -path "*/.git/*" \
        ! -path "*/node_modules/*" \
        ! -path "*/dev/*" \
        ! -name "*.sh" \
        ! -name "*.po" \
        ! -name "*.pot" \
        > "$temp_list")

    (cd "$PARENT_DIR" && zip -q -r "$archive_path" -@ < "$temp_list")
    local result=$?

    rm "$temp_list"

    if [ $result -eq 0 ]; then
        local size=$(du -h "$archive_path" | cut -f1)
        print_success "Archive created: ${RELEASES_DIR}/${archive_name} ($size)"
    else
        print_error "Failed to create archive"
        exit 1
    fi
}

main() {
    echo ""
    echo "╔═══════════════════════════════════╗"
    echo "║   LRob Age Gate - Release Builder   ║"
    echo "╚═══════════════════════════════════╝"
    echo ""

    print_status "Script directory: $SCRIPT_DIR"
    print_status "Releases directory: $RELEASES_DIR"
    echo ""

    check_dependencies

    VERSION=$(get_version)
    print_status "Current version: $VERSION"
    echo ""

    generate_translation_source
    generate_pot
    compile_translations
    create_archive "$VERSION"

    echo ""
    print_success "Release $VERSION completed successfully! 🎉"
    echo ""
    echo "Next steps:"
    echo "  1. Test: unzip ${RELEASES_DIR}/${PLUGIN_SLUG}-${VERSION}.zip"
    echo "  2. Upload to WordPress"
    echo ""
}

main "$@"
