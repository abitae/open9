export function allowsNegativeStock(site) {
    return site?.store?.allow_negative_stock === true;
}

export function isOutOfStock(product, site) {
    if (allowsNegativeStock(site)) {
        return false;
    }

    return product?.stock !== null && product.stock <= 0;
}

export function stockQuantityMax(product, site) {
    if (allowsNegativeStock(site) || typeof product?.stock !== 'number') {
        return null;
    }

    return product.stock;
}
