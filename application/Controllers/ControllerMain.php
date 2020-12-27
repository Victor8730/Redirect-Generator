<?php

declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Core\Route;
use Exceptions\NotExistFileFromUrlException;
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
     * Else generate redirect file
     */
    public function actionGenerate(): void
    {
        $strOutside = $this->validate();
        $dataFromUrl = $this->getDataFromUrl($strOutside, $strOutside['type']);

        $result = 'RewriteEngine On' . "\n";

        if ($strOutside['type'] == 1) {

            if (!empty($dataFromUrl)) {
                $oldDomains = $dataFromUrl->url->loc[0];
                foreach ($dataFromUrl->url as $item) {
                    $oldUrl = str_replace($oldDomains, '', $item->loc);
                    $newUrl = str_replace($oldDomains, $strOutside['domains'], $item->loc);
                    $result .= $this->getType($oldUrl['path'],$newUrl['path'] ,$strOutside['type-redirect']);
                }
            } else {
                $this->isAjax ? $this->ajaxResponse(false, 'Url not exist, check url!') : Route::errorPage404();
            }
        } else {

            if(($handle = fopen($dataFromUrl, "r")) !== FALSE){
                while (($data = fgetcsv($handle, 10000, ";")) !== FALSE) {
                    $oldUrl = parse_url($data[0]);
                    $newUrl = parse_url($data[1]);
                    $result .= $this->getType($oldUrl['path'],$newUrl['path'] ,$strOutside['type-redirect']);
                }
                fclose($handle);
            }
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
        $root = $dom->createElement('urlset');
        $movie_node = $dom->createElement('url');
        $child_node_title = $dom->createElement('loc', 'https://site.com/testurlforexample');
        $movie_node->appendChild($child_node_title);
        $child_node_year = $dom->createElement('lastmod', '2018-09-25T18:03:49+01:00');
        $movie_node->appendChild($child_node_year);
        $childNodeLng = $dom->createElement('priority', '1');
        $movie_node->appendChild($childNodeLng);
        $root->appendChild($movie_node);
        $dom->appendChild($root);

        return $dom->saveXML();
    }

    /**
     * Get data
     * @param array $dataOutside
     * @param int $type
     * @return false|\SimpleXMLElement|string[]
     */
    private function getDataFromUrl(array $dataOutside, int $type)
    {
        try {
            $this->model->validator->checkFileExistFromUrl($dataOutside['url']);
            libxml_use_internal_errors(true);
            $rowsFromFile = ($type === 1) ? simplexml_load_file($dataOutside['url']) : $dataOutside['url'];
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
        $exampleUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/main/examplexml';
        $urlData = (isset($_POST['url-data']) && !empty($_POST['url-data'])) ? $_POST['url-data'] : $exampleUrl;
        $newDomains = $_POST['new-domains'] ?? 'https://webpagestudio.net';
        $type = (int)$_POST['type-input-data'] ?? '1';
        $typeRedirect = (int)$_POST['type-redirect'] ?? '1';

        try {
            $urlData = $this->model->validator->checkStr($urlData);
            $newDomains = $this->model->validator->checkStr($newDomains);
        } catch (NotValidInputException $e) {
            echo $e->getMessage();
        }

        return ['url' => $urlData, 'domains' => $newDomains, 'type' => $type, 'type-redirect' => $typeRedirect];
    }

    private function getType(string $from,string $to,int $type):string{
        return ($type === 1) ? 'RewriteRule ^' . $from . ' ' . $to .' [L]'. "\n" : 'Redirect 301 /' . $from . ' ' . $to . "\n";
    }
}