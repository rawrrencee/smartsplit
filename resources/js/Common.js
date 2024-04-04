export const getImgSrcFromPath = (path) => {
    return route("photo") + `?img_path=${path}`;
};
