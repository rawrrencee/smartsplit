<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\GlobalSettings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class CommonController extends Controller
{
    protected $COMPANY_NAME = 'COMPANY_NAME';
    protected $HardcodedDataController;

    public function __construct(HardcodedDataController $HardcodedDataController)
    {
        $this->HardcodedDataController = $HardcodedDataController;
    }

    public function getRoutes()
    {
        return collect(Route::getRoutes())->map(function ($route) {
            return $route->uri();
        });
    }

    public function to2DecimalPlacesIfValid($value)
    {
        $floatVal = floatval($value);
        if (is_numeric($floatVal) && !is_nan($floatVal)) {
            return number_format($floatVal, 2, '.', ',');
        }
        return isset($value) ? number_format(0.0, 2, '.', ',') : null;
    }

    public function showPhoto(Request $request)
    {
        return response()->file(storage_path('app/private/' . $request['img_path']));
    }

    public function deletePhoto($img_path)
    {
        if (isset($img_path) && Storage::disk('private')->exists($img_path)) {
            Storage::disk('private')->delete($img_path);
            return true;
        }
        return false;
    }

    function findValueByKey($array, $searchKey, $keyName = 'key', $valueName = 'value')
    {
        foreach ($array as $item) {
            if ($item[$keyName] === $searchKey) {
                return $item[$valueName];
            }
        }
        return null;
    }

    public function formatUtcDateToSingaporeDate(?string $dateString)
    {
        if (empty($dateString)) return null;

        $dateInUtc = Carbon::createFromFormat($this->HardcodedDataController->getFrontendUtcDateTimeFormat(), $dateString, 'UTC');
        $dateInSingapore = $dateInUtc->setTimezone($this->HardcodedDataController->getTimezone())->format($this->HardcodedDataController->getSqlDateTimeFormat());

        return $dateInSingapore;
    }

    public function formatUtcDateToSqlDate(?string $dateString)
    {
        if (empty($dateString)) return null;

        $dateInUtc = Carbon::createFromFormat($this->HardcodedDataController->getFrontendUtcDateTimeFormat(), $dateString, 'UTC')->format($this->HardcodedDataController->getSqlDateTimeFormat());

        return $dateInUtc;
    }

    public function formatSqlDateTimeToSingaporeDate(?string $dateString)
    {
        if (empty($dateString)) return null;

        $dateInUTC = Carbon::createFromFormat($this->HardcodedDataController->getSqlDateTimeFormat(), $dateString, 'UTC');

        $dateInSingaporeTimezone = $dateInUTC->setTimezone($this->HardcodedDataController->getTimezone());

        return $dateInSingaporeTimezone;
    }

    public function formatException(\Exception $e)
    {
        return 'Message: ' . $e->getMessage() . ' Line: ' . $e->getLine();
    }

    public function maskEmail($email)
    {
        // Split the email address into local and domain parts
        list($localPart, $domainPart) = explode('@', $email);

        // Get the length of the local part (before the '@' symbol)
        $localPartLength = strlen($localPart);

        // Determine the number of characters to display before truncation
        $displayCharacters = min(3, $localPartLength); // Display at least 3 characters

        // Create the masked email address
        $maskedLocalPart = substr($localPart, 0, $displayCharacters) . str_repeat('*', $localPartLength - $displayCharacters);

        // Return the masked email address
        return $maskedLocalPart . '@' . $domainPart;
    }

    public function maskUsername($username)
    {
        // Get the length of the username
        $usernameLength = strlen($username);

        // Determine the number of characters to display before truncation
        $displayCharacters = min(3, $usernameLength); // Display at least 3 characters

        // Create the masked username
        $maskedUsername = substr($username, 0, $displayCharacters) . str_repeat('*', $usernameLength - $displayCharacters);

        // Return the masked username
        return $maskedUsername;
    }

    public function handleException(\Exception $e, String $type = 'default', String $messageModifier = 'update')
    {
        return redirect()->back()
            ->with('show', true)
            ->with('type', $type)
            ->with('status', 'error')
            ->with('message', 'Failed to ' . $messageModifier . ' record: ' . $this->formatException($e));
    }

    public function redirectBackWithGenericError()
    {
        return redirect()->back()
            ->with('show', true)
            ->with('type', 'default')
            ->with('status', 'error')
            ->with('message', 'An error occurred.');
    }
}
