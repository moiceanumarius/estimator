#!/bin/bash

# Start supervisor (which will start the WebSocket server)
/usr/bin/supervisord -c /etc/supervisor/supervisord.conf &

# Start Apache
apache2-foreground

