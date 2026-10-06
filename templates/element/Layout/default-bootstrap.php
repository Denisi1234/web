        <script>
            window.FASTNET_API_URL = (
                window.location.hostname === 'localhost' ||
                window.location.hostname === '127.0.0.1' ||
                window.location.hostname === ''
            ) ? 'http://127.0.0.1:8000' : 'https://api.fastnetstays.com';

            window.API_URL = function (path) {
                return window.FASTNET_API_URL + path;
            };

            // Mapbox configuration — public pk.* token only (never echo secret sk.*). Restrict token by HTTP Referrer in Mapbox dashboard.
            <?php
            $layoutMapboxToken = $mapboxToken ?? \Cake\Core\Configure::read('App.mapboxToken', env('MAPBOX_TOKEN', ''));
            $layoutMapboxStyle = $mapboxStyle ?? \Cake\Core\Configure::read('App.mapboxStyle', 'mapbox://styles/mapbox/streets-v12');
            if (!is_string($layoutMapboxToken) || !str_starts_with($layoutMapboxToken, 'pk.')) $layoutMapboxToken = '';
            if (!is_string($layoutMapboxStyle) || $layoutMapboxStyle === '') $layoutMapboxStyle = 'mapbox://styles/mapbox/streets-v12';
            // Only pk.* public tokens are echoed to HTML; secrets never leave server.
            ?>
            window.MAPBOX_TOKEN = <?= json_encode($layoutMapboxToken) ?> || window.MAPBOX_TOKEN || '';
            window.MAPBOX_STYLE = <?= json_encode($layoutMapboxStyle) ?> || window.MAPBOX_STYLE || 'mapbox://styles/mapbox/streets-v12';
            var _isMapboxStyle = window.MAPBOX_STYLE && window.MAPBOX_STYLE.indexOf('mapbox://') === 0;
            if (!window.MAPBOX_TOKEN && _isMapboxStyle) {
                window.MAPBOX_STYLE = 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json';
            }
            window.DEFAULT_MAPBOX_TOKEN = window.MAPBOX_TOKEN || '';
            if (window.MAPBOX_TOKEN && typeof mapboxgl !== 'undefined') {
                mapboxgl.accessToken = window.MAPBOX_TOKEN;
            }
            if (window.MAPBOX_TOKEN) {
                window.DEFAULT_MAPBOX_TOKEN = window.MAPBOX_TOKEN;
                if (typeof mapboxgl !== 'undefined') {
                    mapboxgl.accessToken = window.MAPBOX_TOKEN;
                }
            }
            // always dispatch — gh-home-map.js handles OSM fallback without token
            setTimeout(function(){ window.dispatchEvent(new CustomEvent('fastnet:mapbox-ready')); }, 0);

            // Fallback runtime fetch if token not server-injected (e.g. other pages or env missing)
            if (!window.MAPBOX_TOKEN) {
                fetch(window.API_URL('/api/map-config'))
                    .then(res => res.json())
                    .then(data => {
                        const tok = data && (data.mapbox_token || data.mapboxToken || data.token || (data.data && data.data.mapbox_token));
                        const sty = data && (data.mapbox_style || data.style);
                        if (tok && tok !== 'YOUR_MAPBOX_ACCESS_TOKEN' && tok !== 'pk.placeholder' && tok !== '' && tok.indexOf('pk.')===0) {
                            window.MAPBOX_TOKEN = tok;
                            window.DEFAULT_MAPBOX_TOKEN = tok;
                            if (sty) window.MAPBOX_STYLE = sty;
                            if (typeof mapboxgl !== 'undefined') {
                                mapboxgl.accessToken = tok;
                            }
                            window.dispatchEvent(new CustomEvent('fastnet:mapbox-ready'));
                        } else if (sty) {
                            window.MAPBOX_STYLE = sty;
                            window.dispatchEvent(new CustomEvent('fastnet:mapbox-ready'));
                        }
                    })
                    .catch(err => console.warn('Mapbox config error:', err));
            }

            // Global password toggle helper function
            function togglePasswordVisibility(fieldId, iconEl) {
                const input = document.getElementById(fieldId);
                if (!input) return;
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                
                const icon = iconEl.querySelector('i');
                if (icon) {
                    if (isPassword) {
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                }
            }
        </script>
