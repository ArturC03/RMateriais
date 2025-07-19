#!/bin/bash

echo "🔍 Checking installed and running notification daemons..."

daemons=("mako" "dunst" "swaync" "deadd-notification-center" "muffin-notification-daemon")

for daemon in "${daemons[@]}"; do
    echo -e "\n🧩 $daemon:"

    # Check if installed (via pacman or yay)
    if pacman -Q $daemon &>/dev/null || yay -Q $daemon &>/dev/null; then
        echo "✅ Installed"
    else
        echo "❌ Not installed"
    fi

    # Check if running
    if pgrep -x $daemon &>/dev/null; then
        echo "🟢 Running"
    else
        echo "🔴 Not running"
    fi
done
