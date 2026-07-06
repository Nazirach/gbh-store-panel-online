(function () {
    'use strict';

    if (!window.firebase) {
        console.error('Firebase SDK is not loaded before firebase-init.js');
        return;
    }

    function hasDefaultFirebaseApp() {
        try {
            if (firebase.apps && firebase.apps.length > 0) {
                return true;
            }
            if (typeof firebase.app === 'function') {
                firebase.app();
                return true;
            }
        } catch (e) {
            return false;
        }
        return false;
    }

    if (hasDefaultFirebaseApp()) {
        return;
    }

    var firebaseConfig = {
        apiKey: 'AIzaSyCYaa2ILmCFYLhWNEE3MWF6h74ECqhWZ38',
        authDomain: 'erbete-putra.firebaseapp.com',
        databaseURL: 'https://erbete-putra-default-rtdb.asia-southeast1.firebasedatabase.app',
        projectId: 'erbete-putra',
        storageBucket: 'erbete-putra.appspot.com',
        messagingSenderId: '620343172253',
        appId: '1:620343172253:web:a7acb1cdd998c095414874',
        measurementId: 'G-8FNRCWJ466'
    };

    try {
        firebase.initializeApp(firebaseConfig);
    } catch (e) {
        if (e && (e.code === 'app/duplicate-app' || String(e.message || '').indexOf('already exists') !== -1)) {
            console.warn('Firebase default app already initialized; continuing.');
            return;
        }
        throw e;
    }
})();
