## "KML-Generator" - mini application with MVC structure

### The task

Generating kml file for adding locations on google maps:
- Data obtained from the link in the form of a csv or xml file;


### How the app works

- On the main page there is a line for entering a link where the data is located. 
Enter the link.
- Below is a button to create a file. 
Click on the button, if the link is not entered, a file with default data is created.


### Change settings

- If the input data format is different from the default data, for example, the number of fields is larger, then you need to correct the array generation method in the main controller

#### Example change setting 

1. If the csv file includes more input fields: `1; Dnepr; 48.4786954; 35.021489'; addition desc; info; other`
    - change "application/Controllers/ControllerMain.php"
        ```
      public function createArrayFromCsv(){
           ... 
           $countFields = 7;
           ...
            $arrItemCsv = str_getcsv($row, ';');
            $inside['id'] = trim($arrItemCsv[0]);
            $inside['name'] = $this->trimSpecialCharacters(trim($arrItemCsv[1]));
            $inside['lat'] = trim($arrItemCsv[2]);
            $inside['lng'] = trim($arrItemCsv[3]);
            $inside['add_desc'] = trim($arrItemCsv[4]);
            $inside['info'] = trim($arrItemCsv[5]);
            $inside['other'] = trim($arrItemCsv[6]);
            $array[$arrItemCsv[0]] = $inside;
      }
        ```
   - change template "application/Views/kml/desc.twig"
        ```
        <div>
            <p>{{ id }}:{{ name }}</p>
            <p>{{ lng }}:{{ lat }}</p>
            <p>{{ add_desc }}:{{ info }}</p>
            <p>{{ other }}</p>
        </div>
        ```
1. If the xml file includes more element:  
    ```<?xml version="1.0" encoding="utf-8"?>
    <document>
      <station>
        <id>1</id>
        <name>Dnepr</name>
        <lng>35.021489</lng>
        <lat>48.4786954</lat>
        <add_desc>addition desc</add_desc>
        <info>info text</info>
        <other>other text</other>
      </station>
    </document>
    ```
    - change "application/Controllers/ControllerMain.php"
        ```
      public function createArrayFromXml(){
            ...
            $countFields = 7;
            ...
            $key = (string)$item->id;
            $array[$key]['id'] = $key;
            $array[$key]['name'] = $this->trimSpecialCharacters((string)$item->name);
            $array[$key]['lng'] = (string)$item->lng;
            $array[$key]['lat'] = (string)$item->lat;
            $array[$key]['add_desc'] = (string)$item->add_desc;
            $array[$key]['info'] = (string)$item->info;
            $array[$key]['other'] = (string)$item->other;
      }
        ```
   - change template "application/Views/kml/desc.twig"
        ```
        <div>
            <p>{{ id }}:{{ name }}</p>
            <p>{{ lng }}:{{ lat }}</p>
            <p>{{ add_desc }}:{{ info }}</p>
            <p>{{ other }}</p>
        </div>
        ```

