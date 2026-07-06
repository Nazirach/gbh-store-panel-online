(function () {
    'use strict';

    if (window.__GHALBIT_FIRESTORE_WRITE_NORMALIZER__) {
        return;
    }
    window.__GHALBIT_FIRESTORE_WRITE_NORMALIZER__ = true;

    function isPlainObject(value) {
        return value && typeof value === 'object' && !Array.isArray(value) && !(value instanceof Date);
    }

    function cloneData(data) {
        if (!isPlainObject(data)) {
            return data;
        }
        return Object.assign({}, data);
    }

    function toNumberIfNumeric(value) {
        if (value === '' || value === null || value === undefined) {
            return value;
        }
        if (typeof value === 'number') {
            return value;
        }
        if (typeof value === 'string') {
            var cleaned = value.replace(/,/g, '').trim();
            if (cleaned === '') {
                return value;
            }
            var parsed = Number(cleaned);
            return Number.isFinite(parsed) ? parsed : value;
        }
        return value;
    }

    function mirrorSectionFields(data) {
        if (!isPlainObject(data)) {
            return data;
        }
        var section = data.section_id || data.sectionId || data.sectionID || '';
        if (section !== '') {
            data.section_id = section;
            data.sectionId = section;
        }
        return data;
    }

    function mirrorPhotoFields(data) {
        if (!isPlainObject(data)) {
            return data;
        }
        if ((!data.photo || data.photo === '') && Array.isArray(data.photos) && data.photos.length > 0) {
            data.photo = data.photos[0];
        }
        if (data.photo && (!Array.isArray(data.photos) || data.photos.length === 0)) {
            data.photos = [data.photo];
        }
        return data;
    }

    function mirrorCommissionFields(data) {
        if (!isPlainObject(data)) {
            return data;
        }
        if (data.adminCommission !== undefined && data.adminCommision === undefined) {
            data.adminCommision = data.adminCommission;
        }
        if (data.adminCommision !== undefined && data.adminCommission === undefined) {
            data.adminCommission = data.adminCommision;
        }
        return data;
    }

    function normalizeVendorCategories(data) {
        if (!isPlainObject(data)) {
            return data;
        }
        if (data.categoryID === undefined || data.categoryID === null || data.categoryID === '') {
            data.categoryID = [];
        } else if (!Array.isArray(data.categoryID)) {
            data.categoryID = [data.categoryID];
        }
        return data;
    }

    function normalizeProductCategory(data) {
        if (!isPlainObject(data)) {
            return data;
        }
        if (Array.isArray(data.categoryID)) {
            data.categoryID = data.categoryID.length > 0 ? data.categoryID[0] : '';
        }
        return data;
    }

    function normalizeProductNumbers(data) {
        if (!isPlainObject(data)) {
            return data;
        }
        ['price', 'disPrice', 'quantity'].forEach(function (key) {
            if (Object.prototype.hasOwnProperty.call(data, key)) {
                data[key] = toNumberIfNumeric(data[key]);
            }
        });

        if (data.item_attribute && Array.isArray(data.item_attribute.variants)) {
            data.item_attribute = Object.assign({}, data.item_attribute);
            data.item_attribute.variants = data.item_attribute.variants.map(function (variant) {
                if (!isPlainObject(variant)) {
                    return variant;
                }
                var nextVariant = Object.assign({}, variant);
                ['variant_price', 'variant_quantity', 'price', 'quantity'].forEach(function (key) {
                    if (Object.prototype.hasOwnProperty.call(nextVariant, key)) {
                        nextVariant[key] = toNumberIfNumeric(nextVariant[key]);
                    }
                });
                return nextVariant;
            });
        }
        return data;
    }

    function normalizeCollectionData(collectionName, data, docRef) {
        var next = cloneData(data);
        if (!isPlainObject(next)) {
            return Promise.resolve(data);
        }

        if (collectionName === 'users') {
            mirrorSectionFields(next);
            return Promise.resolve(next);
        }

        if (collectionName === 'vendors') {
            mirrorSectionFields(next);
            mirrorPhotoFields(next);
            mirrorCommissionFields(next);
            normalizeVendorCategories(next);
            if ((!next.id || next.id === '') && docRef && docRef.id) {
                next.id = docRef.id;
            }
            return Promise.resolve(next);
        }

        if (collectionName === 'vendor_products') {
            mirrorSectionFields(next);
            mirrorPhotoFields(next);
            normalizeProductCategory(next);
            normalizeProductNumbers(next);

            if (next.vendorID && window.firebase && firebase.firestore) {
                return firebase.firestore().collection('vendors').doc(next.vendorID).get().then(function (vendorSnap) {
                    if (vendorSnap && vendorSnap.exists) {
                        var vendor = vendorSnap.data() || {};
                        if (next.zoneId === undefined && vendor.zoneId !== undefined) {
                            next.zoneId = vendor.zoneId;
                        }
                        if (next.adminCommission === undefined && vendor.adminCommission !== undefined) {
                            next.adminCommission = vendor.adminCommission;
                        }
                        if (next.adminCommision === undefined && vendor.adminCommision !== undefined) {
                            next.adminCommision = vendor.adminCommision;
                        }
                        mirrorCommissionFields(next);
                    }
                    return next;
                }).catch(function () {
                    return next;
                });
            }
            return Promise.resolve(next);
        }

        if (collectionName === 'story') {
            mirrorSectionFields(next);
            return Promise.resolve(next);
        }

        return Promise.resolve(next);
    }

    function getCollectionName(docRef) {
        try {
            if (docRef && docRef.parent && docRef.parent.id) {
                return docRef.parent.id;
            }
            if (docRef && docRef.path) {
                return String(docRef.path).split('/')[0];
            }
        } catch (e) {}
        return '';
    }

    function patchFirestoreDocumentPrototype() {
        if (!window.firebase || !firebase.firestore) {
            return false;
        }

        var probe = firebase.firestore().collection('__ghalbit_probe__').doc('__normalizer__');
        var proto = Object.getPrototypeOf(probe);

        if (!proto || proto.__ghalbitWriteNormalizerPatched) {
            return true;
        }

        var originalSet = proto.set;
        var originalUpdate = proto.update;

        proto.set = function (data, options) {
            var ref = this;
            var collectionName = getCollectionName(ref);
            return normalizeCollectionData(collectionName, data, ref).then(function (normalized) {
                if (arguments.length >= 2 || options !== undefined) {
                    return originalSet.call(ref, normalized, options);
                }
                return originalSet.call(ref, normalized);
            });
        };

        proto.update = function () {
            var ref = this;
            var args = Array.prototype.slice.call(arguments);
            var collectionName = getCollectionName(ref);

            if (args.length === 1 && isPlainObject(args[0])) {
                return normalizeCollectionData(collectionName, args[0], ref).then(function (normalized) {
                    return originalUpdate.call(ref, normalized);
                });
            }

            return originalUpdate.apply(ref, args);
        };

        proto.__ghalbitWriteNormalizerPatched = true;
        console.info('[GHALBIT] Firestore write normalizer active.');
        return true;
    }

    function boot() {
        try {
            if (!patchFirestoreDocumentPrototype()) {
                setTimeout(boot, 300);
            }
        } catch (e) {
            console.warn('[GHALBIT] Firestore write normalizer skipped:', e);
        }
    }

    boot();
})();
