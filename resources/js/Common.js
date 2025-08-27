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
    WrenchIcon
} from "@heroicons/vue/24/outline";
import { router } from "@inertiajs/vue3";

export const kAllCurrenciesKey = "allCurrencies";
export const kDefaultExpenseGroupKey = "addExpenseDefaultGroupId";
export const kDefaultExpenseCurrencyKey = "addExpenseDefaultCurrency";
export const kRememberRecentGroupKey = "recentGroup";
export const kRememberRecentGroupNameKey = "recentGroupName";

export const setRememberRecentGroup = (groupId, groupName) => {
    if (groupId) {
        localStorage.setItem(kRememberRecentGroupKey, groupId);
        localStorage.setItem(kRememberRecentGroupNameKey, groupName);
    } else {
        localStorage.removeItem(kRememberRecentGroupKey);
        localStorage.removeItem(kRememberRecentGroupNameKey);
    }
};
export const getRememberRecentGroup = () => {
    return {
        id: localStorage.getItem(kRememberRecentGroupKey),
        name: localStorage.getItem(kRememberRecentGroupNameKey)
    };
};

export const getAllCurrencies = () => {
    const allCurrencies = localStorage.getItem(kAllCurrenciesKey);
    if (allCurrencies) {
        return JSON.parse(allCurrencies);
    } else {
        return [];
    }
};
export const setAllCurrencies = (currencies) => {
    if (currencies) {
        localStorage.setItem(kAllCurrenciesKey, JSON.stringify(currencies));
    }
};

export const getImgSrcFromPath = (path) => {
    return route("photo") + `?img_path=${path}`;
};

export const showToastIfNeeded = (toast, flash) => {
    if (flash.show) {
        if (flash.status === "error") {
            toast.error(flash.message);
        } else {
            if (flash.route && flash.id && route().has(flash.route)) {
                toast.success(flash.message, {
                    action: {
                        label: "View",
                        onClick: () => router.get(route(flash.route), { id: flash.id })
                    }
                });
                return;
            }
            toast.success(flash.message);
        }
    }
};

export const distributeEqually = (amount, people) => {
    // Round the input amount to 2 decimal places
    const roundedAmount = Math.round(amount * 100) / 100;
    
    // Calculate base share per person
    const baseShare = Math.floor((roundedAmount * 100) / people) / 100;
    
    // Create initial distribution with base shares
    const distribution = Array(people).fill(baseShare);
    
    // Calculate the total distributed so far
    const totalDistributed = baseShare * people;
    
    // Calculate the remaining amount due to rounding
    const remaining = Math.round((roundedAmount - totalDistributed) * 100) / 100;
    
    // Distribute the remaining amount as evenly as possible
    if (remaining > 0) {
        const remainingCents = Math.round(remaining * 100);
        
        // Randomly select people to get extra cents
        const extraIndices = Array.from({ length: people }, (_, i) => i)
            .sort(() => Math.random() - 0.5)
            .slice(0, remainingCents);
        
        extraIndices.forEach(index => {
            distribution[index] = Math.round((distribution[index] + 0.01) * 100) / 100;
        });
    }

    return distribution;
};

export const to2DecimalPlacesIfValid = (value, withSeparator = true) => {
    const floatVal = Number.parseFloat(value);
    if (typeof floatVal === "number" && !isNaN(floatVal)) {
        return withSeparator ? floatVal.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, "$&,") : floatVal.toFixed(2);
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

export const generateUUID = (a) => {
    return a ? (a ^ Math.random() * 16 >> a / 4).toString(16) : ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, generateUUID);
};
