export const getImgSrcFromPath = (path) => {
    return route("photo") + `?img_path=${path}`;
};

export const showToastIfNeeded = (toast, flash) => {
    if (flash.show) {
        if (flash.status === "error") toast.error(flash.message);
        else toast.success(flash.message);
    }
};

export const to2DecimalPlacesIfValid = (value) => {
    const floatVal = Number.parseFloat(value);
    if (typeof floatVal === "number" && !isNaN(floatVal)) {
        return floatVal.toFixed(2);
    }
    return value ?? (0.0).toFixed(2);
};
