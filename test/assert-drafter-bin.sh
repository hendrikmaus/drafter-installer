#!/usr/bin/env bash
if test "$BASH" = "" || "$BASH" -uc "a=();true \"\${a[@]}\"" 2>/dev/null; then
    # Bash 4.4, Zsh
    set -euo pipefail
else
    # Bash 4.3 and older chokes on empty arrays with set -u.
    set -eo pipefail
fi

readonly original_bin="vendor/apiaryio/drafter/bin/drafter"
readonly symlinked_bin="vendor/bin/drafter"

echo "Checking ${original_bin}"
if [ ! -f "${original_bin}" ]; then
    echo "`${original_bin}` not found"
    exit 1
fi

echo "Checking ${symlinked_bin}"
if [ ! -f "${symlinked_bin}" ]; then
    echo "`${symlinked_bin}` not found"
    exit 1
fi

readlink "${symlinked_bin}"