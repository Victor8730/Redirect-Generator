<?php

declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Core\Route;
use Exceptions\NotExistFileFromUrlException;
use Exceptions\NotValidDataFromUrlException;
use Exceptions\NotValidInputException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;


class ControllerMain extends Controller
{
    /**
     * Show page with template main
     */
    public function actionIndex()
    {
        try {
            echo $this->view->render('main/' . $this->getNameView());
        } catch (LoaderError $e) {
        } catch (RuntimeError $e) {
        } catch (SyntaxError $e) {
        }
    }

    /**
     * Show page with example xml data
     */
    public function actionExampleXml(): void
    {
        echo $this->exampleXml();
    }

    /**
     * If ajax is a request, then we will return a json response
     * Else generate kml file
     */
    public function actionGenerate(): void
    {
        $strOutside = $this->validate();
        $dataFromUrl = $this->getDataFromUrl($strOutside);

        $result = 'RewriteEngine On'. "\n";

        if (!empty($dataFromUrl)) {
            $oldDomains = $dataFromUrl->url->loc[0];
            foreach ($dataFromUrl->url as $item) {
                $newUrl = str_replace($oldDomains, $strOutside['domains'], $item->loc);
                $result .= 'Redirect 301 ' . $item->loc . ' ' . $newUrl . "\n";
            }
        } else {
            $this->isAjax ? $this->ajaxResponse(false, 'Url not exist, check url!') : Route::errorPage404();
        }

         header('Content-type: text/html; charset=utf-8');
         header('Content-disposition: attachment; filename=Redirect301_' . date("Ymd_His") . '.txt');

        if ($this->isAjax) {
            $this->ajaxResponse(true, 'It’s ok!');
        } else {
            echo $result;
        }
    }

    /**
     * generate example xml
     * @return string
     */
    public function exampleXml(): string
    {
        $dom = new \DOMDocument();
        $dom->encoding = 'utf-8';
        $dom->xmlVersion = '1.0';
        $dom->formatOutput = true;
        $root = $dom->createElement('document');
        $movie_node = $dom->createElement('station');
        $child_node_title = $dom->createElement('id', '1');
        $movie_node->appendChild($child_node_title);
        $child_node_year = $dom->createElement('name', 'Dnepr');
        $movie_node->appendChild($child_node_year);
        $childNodeLng = $dom->createElement('lng', '35.021489');
        $movie_node->appendChild($childNodeLng);
        $childNodeLat = $dom->createElement('lat', '48.4786954');
        $movie_node->appendChild($childNodeLat);
        $root->appendChild($movie_node);
        $dom->appendChild($root);

        return $dom->saveXML();
    }

    /**
     * Get data
     * @param array $dataOutside
     * @return false|\SimpleXMLElement|string[]
     */
    private function getDataFromUrl(array $dataOutside)
    {
        try {
            $this->validator->checkFileExistFromUrl($dataOutside['url']);
            libxml_use_internal_errors(true);
            $rowsFromFile = simplexml_load_file($dataOutside['url']);
        } catch (NotExistFileFromUrlException $e) {
            return null;
        }

        return $rowsFromFile;
    }

    /**
     * Delete special characters
     * @param string $str
     * @return string
     */
    public function trimSpecialCharacters(string $str): string
    {
        $replace = preg_replace('/&(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '', $str);

        return str_replace('’', '', $replace);
    }

    /**
     * Check data validity
     * @return array
     */
    private function validate(): array
    {
        $exampleUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/main/examplesitemaps';
        $urlData = (isset($_POST['url-data']) && !empty($_POST['url-data'])) ? $_POST['url-data'] : $exampleUrl;
        $newDomains = $_POST['new-domains'] ?? 'https://webpagestudio.net';

        try {
            $urlData = $this->validator->checkStr($urlData);
            $newDomains = $this->validator->checkStr($newDomains);
        } catch (NotValidInputException $e) {
            echo $e->getMessage();
        }

        return ['url' => $urlData, 'domains' => $newDomains];
    }
}