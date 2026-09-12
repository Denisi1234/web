/**
 * FastNetMap Core — Single Source of Truth for Native Mapbox GL Integration
 * Uses real Mapbox GL JS vector maps (mapbox://styles/mapbox/streets-v12) & Mapbox Static API.
 */
(function (window) {
    'use strict';

    var DEFAULT_MAPBOX_STYLE = 'mapbox://styles/mapbox/streets-v12';

    var FastNetMap = {
        /**
         * Get active Mapbox Public Access Token
         */
        getToken: function () {
            var token = window.MAPBOX_TOKEN || window.DEFAULT_MAPBOX_TOKEN || '';
            if (typeof token === 'string' && token.trim() !== '') {
                return token.trim();
            }
            return '';
        },

        /**
         * Get active Mapbox Style URL
         */
        getStyle: function () {
            return window.MAPBOX_STYLE || DEFAULT_MAPBOX_STYLE;
        },

        /**
         * Generate Mapbox Static Map API image URL
         */
        getStaticMapUrl: function (lat, lng, zoom, width, height) {
            var token = this.getToken();
            zoom = zoom || 13;
            width = width || 300;
            height = height || 300;
            lat = parseFloat(lat) || -6.7725;
            lng = parseFloat(lng) || 39.2450;

            return 'https://api.mapbox.com/styles/v1/mapbox/streets-v12/static/pin-s+1a73e8(' +
                lng + ',' + lat + ')/' + lng + ',' + lat + ',' + zoom + ',0/' + width + 'x' + height +
                '@2x?access_token=' + token;
        },

        /**
         * Initialize native Mapbox GL map instance
         */
        createMap: function (containerId, options) {
            options = options || {};
            var token = this.getToken();
            var style = options.style || this.getStyle();

            if (typeof mapboxgl !== 'undefined') {
                mapboxgl.accessToken = token;
            } else {
                console.error('[FastNetMap] Mapbox GL JS library not loaded');
                return null;
            }

            var map = new mapboxgl.Map({
                container: containerId,
                style: style,
                center: [options.lng || 39.2450, options.lat || -6.7725],
                zoom: options.zoom || 13,
                attributionControl: false
            });

            map.addControl(new mapboxgl.AttributionControl({ compact: true }), 'bottom-right');

            return map;
        }
    };

    window.FastNetMap = FastNetMap;
})(window);
