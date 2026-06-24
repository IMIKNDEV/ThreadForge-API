#!/usr/bin/env sh

dir=$(dirname "$(realpath "$0" 2>/dev/null || readlink -f "$0" 2>/dev/null || echo "$0")")
exec "${dir}/vendor/bin/sail" "$@"
