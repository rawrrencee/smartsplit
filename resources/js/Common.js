export const getImgSrcFromPath = (path) => {
    return route("photo") + `?img_path=${path}`;
};

export const showToastIfNeeded = (toast, flash) => {
    if (flash.show) {
        if (flash.status === "error") toast.error(flash.message);
        else toast.success(flash.message);
    }
};
