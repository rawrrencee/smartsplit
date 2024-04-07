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
    if (typeof value === "number" && !isNaN(value) && Number.parseFloat(value) >= 0) {
        return Number.parseFloat(value).toFixed(2);
    }
    return value ?? 0.0;
};
