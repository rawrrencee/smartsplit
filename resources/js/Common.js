import {
    AcademicCapIcon,
    BoltIcon,
    BriefcaseIcon,
    BuildingStorefrontIcon,
    CakeIcon,
    CurrencyDollarIcon,
    GiftIcon,
    LightBulbIcon,
    ListBulletIcon,
    MusicalNoteIcon,
    PaperAirplaneIcon,
    ShoppingBagIcon,
    ShoppingCartIcon,
    TruckIcon,
    UserGroupIcon,
    WrenchIcon,
} from "@heroicons/vue/24/outline";

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

export const getIconForCategory = (category) => {
    switch (category) {
        case "food":
            return CakeIcon;
        case "transport":
            return TruckIcon;
        case "entertainment":
            return MusicalNoteIcon;
        case "utilities":
            return LightBulbIcon;
        case "groceries":
            return ShoppingCartIcon;
        case "shopping":
            return ShoppingBagIcon;
        case "restaurants":
            return BuildingStorefrontIcon;
        case "travel":
            return PaperAirplaneIcon;
        case "rent":
            return CurrencyDollarIcon;
        case "healthcare":
            return UserGroupIcon;
        case "gifts":
            return GiftIcon;
        case "electronics":
            return BoltIcon;
        case "insurance":
            return BriefcaseIcon;
        case "fuel":
            return WrenchIcon;
        case "education":
            return AcademicCapIcon;
    }

    return ListBulletIcon;
};
