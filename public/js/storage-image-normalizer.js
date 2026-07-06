(function () {
    'use strict';

    var fallbackImage = '/images/ghalbit-maritronix-logo.svg';
    var defaultBucket = 'erbete-putra.appspot.com';

    function getBucket() {
        try {
            if (window.firebase && firebase.app) {
                var app = firebase.app();
                if (app && app.options && app.options.storageBucket) {
                    return app.options.storageBucket;
                }
            }
        } catch (e) {}
        return defaultBucket;
    }

    function looksLikeStoragePath(value) {
        if (!value || typeof value !== 'string') {
            return false;
        }
        if (/^https?:\/\//i.test(value) || /^data:/i.test(value) || value.indexOf('/') === 0) {
            return false;
        }
        return value.indexOf('images%2F') !== -1 || value.indexOf('images/') !== -1 || value.indexOf('logo_web_') !== -1;
    }

    function normalizeStoragePath(value) {
        if (!looksLikeStoragePath(value)) {
            return value;
        }

        var path = value;
        try {
            path = decodeURIComponent(path);
        } catch (e) {}

        path = path.replace(/^\/+/, '');

        if (path.indexOf('images/') === -1 && path.indexOf('logo_web_') === 0) {
            path = 'images/' + path;
        }

        return 'https://firebasestorage.googleapis.com/v0/b/' + getBucket() + '/o/' + encodeURIComponent(path) + '?alt=media';
    }

    function fixImage(img) {
        if (!img || img.dataset.storageNormalizerDone === '1') {
            return;
        }

        var raw = img.getAttribute('src') || '';
        var normalized = normalizeStoragePath(raw);

        if (normalized && normalized !== raw) {
            img.setAttribute('src', normalized);
        }

        img.addEventListener('error', function () {
            if (img.dataset.storageFallbackDone === '1') {
                return;
            }
            img.dataset.storageFallbackDone = '1';
            img.setAttribute('src', fallbackImage);
        });

        img.dataset.storageNormalizerDone = '1';
    }

    function scanImages() {
        Array.prototype.forEach.call(document.images || [], fixImage);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scanImages);
    } else {
        scanImages();
    }

    var observer = new MutationObserver(function () {
        scanImages();
    });

    observer.observe(document.documentElement || document.body, {
        childList: true,
        subtree: true
    });
})();
