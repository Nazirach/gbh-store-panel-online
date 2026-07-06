(function () {
    'use strict';

    if (!window.firebase) {
        console.error('Firebase SDK is not loaded before firebase-init.js');
        return;
    }

    if (firebase.apps && firebase.apps.length > 0) {
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

    firebase.initializeApp(firebaseConfig);
})();
