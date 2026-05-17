#!/usr/bin/env bash
set -euo pipefail

# Validate baseline documentation presence for changed (default) or all in-scope files.
# Usage:
#   scripts/check-documentation.sh          # changed files only
#   scripts/check-documentation.sh --all    # all tracked in-scope files

MODE="changed"
if [[ "${1:-}" == "--all" ]]; then
    MODE="all"
fi

STRICT_PLACEHOLDER_CHECK="${DOCS_STRICT_PLACEHOLDER:-0}"

is_in_scope() {
    local file="$1"

    if [[ ! "$file" =~ ^app/.*\.php$ ]] \
        && [[ ! "$file" =~ ^public/js/.*\.js$ ]] \
        && [[ ! "$file" =~ ^public/css/.*\.css$ ]] \
        && [[ ! "$file" =~ ^app/Modules/.*\.js$ ]] \
        && [[ ! "$file" =~ ^app/Modules/.*\.css$ ]]; then
        return 1
    fi

    case "$file" in
        vendor/*|writable/*|node_modules/*|build/*|builds/*|*.min.js|*.min.css)
            return 1
            ;;
    esac

    return 0
}

collect_files() {
    if [[ "$MODE" == "all" ]]; then
        git ls-files
        return
    fi

    if [[ -n "${GITHUB_BASE_REF:-}" ]]; then
        local base_ref="origin/${GITHUB_BASE_REF}"
        if git rev-parse --verify "$base_ref" >/dev/null 2>&1; then
            git diff --name-only "$base_ref...HEAD"
            return
        fi
    fi

    if git rev-parse --verify HEAD~1 >/dev/null 2>&1; then
        git diff --name-only HEAD~1...HEAD
        git diff --name-only --cached
        git diff --name-only
        return
    fi

    git diff --name-only --cached
    git diff --name-only
}

has_file_header_comment() {
    local file="$1"
    awk '
        NR > 40 { exit 1 }
        /^[[:space:]]*$/ { next }
        /^<\?php[[:space:]]*$/ { next }
        /^[[:space:]]*\/\*/ { found=1; exit 0 }
        { exit 1 }
        END { if (found == 1) exit 0; exit 1 }
    ' "$file"
}

has_jsdoc_or_phpdoc() {
    local file="$1"
    grep -Eq '^\s*/\*\*' "$file"
}

has_placeholder_header_text() {
    local file="$1"
    grep -Eq 'File documentation for .*\.' "$file"
}

should_enforce_placeholder_check() {
    if [[ "$MODE" == "changed" ]]; then
        return 0
    fi

    [[ "$STRICT_PLACEHOLDER_CHECK" == "1" ]]
}

FAILURES=()
CHECKED=0

while IFS= read -r file; do
    [[ -z "$file" ]] && continue
    [[ -f "$file" ]] || continue

    if ! is_in_scope "$file"; then
        continue
    fi

    CHECKED=$((CHECKED + 1))

    case "$file" in
        *.php)
            if ! has_file_header_comment "$file"; then
                FAILURES+=("$file: missing top-of-file PHPDoc header")
                continue
            fi

            if should_enforce_placeholder_check && has_placeholder_header_text "$file"; then
                FAILURES+=("$file: file header is placeholder text; replace with domain-specific description")
                continue
            fi

            if grep -Eq '^\s*(public|protected) function ' "$file" && ! has_jsdoc_or_phpdoc "$file"; then
                FAILURES+=("$file: missing PHPDoc blocks for callable members")
            fi
            ;;
        *.js)
            if ! has_file_header_comment "$file"; then
                FAILURES+=("$file: missing top-of-file documentation comment")
                continue
            fi

            if should_enforce_placeholder_check && has_placeholder_header_text "$file"; then
                FAILURES+=("$file: file header is placeholder text; replace with domain-specific description")
                continue
            fi

            if grep -Eq '^\s*(function|async function|const\s+\w+\s*=\s*\(|class\s+\w+)' "$file" && ! has_jsdoc_or_phpdoc "$file"; then
                FAILURES+=("$file: missing JSDoc blocks for functions/classes")
            fi
            ;;
        *.css)
            if ! has_file_header_comment "$file"; then
                FAILURES+=("$file: missing stylesheet scope comment header")
                continue
            fi

            if should_enforce_placeholder_check && has_placeholder_header_text "$file"; then
                FAILURES+=("$file: file header is placeholder text; replace with domain-specific description")
            fi
            ;;
    esac

done < <(collect_files | sort -u)

if [[ "$CHECKED" -eq 0 ]]; then
    echo "No in-scope files to validate."
    exit 0
fi

if [[ "${#FAILURES[@]}" -gt 0 ]]; then
    echo "Documentation check failed:"
    printf ' - %s\n' "${FAILURES[@]}"
    exit 1
fi

echo "Documentation check passed for ${CHECKED} in-scope file(s)."
