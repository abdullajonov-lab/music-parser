<?php

namespace App\Http\Controllers;

use App\Jobs\SaveMusic;
use App\Models\Author;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Illuminate\Http\Request;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class ParseController extends Controller
{

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function parse(Request $request)
    {
        $query = $request->input('query') ?? "ozodbek nazarbekov";
        $host = 'http://localhost:4444/wd/hub';

        $capabilities = DesiredCapabilities::chrome();

        $driver = RemoteWebDriver::create($host, $capabilities);

        $driver->get("https://uzhits.net/xfsearch/{$query}");

        $driver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className("fa-download"))
        );

        $elem = $driver->findElements(WebDriverBy::cssSelector("div[data-track]"));
        $navigation = $driver->findElements(WebDriverBy::cssSelector("div.navigation"));

        $elemArr = [];
        $downloadPath = storage_path('app/downloads/');

        if (!file_exists($downloadPath)) {
            mkdir($downloadPath, 0755, true);
        }

        $per_page = last(explode(" ", $navigation[0]->getText()));
        $artist = $elem[0]->getAttribute('data-artist');

        Author::create([
            'name' => $artist,
            'pre_page' => $per_page
        ]);

        foreach ($elem as $element) {
            $fileUrl = $element->getAttribute('data-track');
            $fileTitle = $element->getAttribute('data-title');

            $fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileTitle);
            if (!str_ends_with(strtolower($fileName), '.mp3')) {
                $fileName .= '.mp3';
            }

            $filePath = $downloadPath . $fileName;
            SaveMusic::dispatch($fileUrl, $filePath);
            $elemArr[] = [
                'url' => $fileUrl,
                'title' => $fileTitle,
                'per_page' => $per_page
            ];
        }

        $driver->quit();

        return response()->json($elemArr);
    }
}
