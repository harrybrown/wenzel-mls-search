<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <title>Wenzel MLS Search</title>
  </head>

  <body>
    <form>
      Select Price Range here: 
      <select required name="listPrice">
      <option disabled selected value> -- select a Price Range -- </option>
<option value="50000-100000">$50,000-$100,000</option> 
  <option value="100000-200000">$100,000-$200,000</option>
  <option value="200000-300000">$200,000-$300,000</option>
  <option value="300000-400000">$300,000-$400,000</option>
  <option value="400000-500000">$400,000-$500,000</option>
  <option value="500,000+">$500,000+</option>
</select>
      <input type="submit" val="Search">
    </form>
<?php
    if (isset ($_REQUEST['listPrice'])) {

        date_default_timezone_set('America/New_York');

        require_once("vendor/autoload.php");

        $log = new \Monolog\Logger('PHRETS');
        $log->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::DEBUG));

        $config = new \PHRETS\Configuration;
        $config->setLoginUrl('http://connectmls-rets.mredllc.com/rets/server/login')
                ->setUsername('RETS_O_21962_2')
                ->setPassword('mcd2kp9ghx')
                ->setRetsVersion('1.7.2');

        $rets = new \PHRETS\Session($config);
        $rets->setLogger($log);

        $connect = $rets->Login();
        if ($connect) {
                echo "  + Connected<br>\n";
        }
        else {
                echo "  + Not connected:<br>\n";
                print_r($rets->Error());
                exit;
        }
        // $system = $rets->GetSystemMetadata();
        // var_dump($system);

        // $resources = $system->getResources();
        // $classes = $resources->first()->getClasses();
        // var_dump($classes);

        // $classes = $rets->GetClassesMetadata('Property');
        // var_dump($classes->first());

        // $objects = $rets->GetObject('Property', 'Photo', '00-1669', '*', 1);
        // var_dump($objects);

        // // $fields = $rets->GetTableMetadata('Property', 'A');
        // // var_dump($fields[0]);

        // $results = $rets->Search('Property', 'A', '*', ['Limit' => 3, 'Select' => 'LIST_1,LIST_105,LIST_15,LIST_22,LIST_87,LIST_133,LIST_134']);
        // foreach ($results as $r) {
        //     var_dump($r);
        // }




        $rets_modtimestamp_field = "LIST_87";
        $property_classes = array("A");
        $previous_start_time = "2015-04-01T00:00:00";

        foreach ($property_classes as $class) {

                echo "+ Property:{$class}<br>\n";

                $file_name = strtolower("property_{$class}.csv");
                $fh = fopen($file_name, "w+");

                $maxrows = true;
                $offset = 1;
                $limit = 100;
                $fields_order = array();
                        $listPrice = $_REQUEST['listPrice'];



                        $query = "({$rets_modtimestamp_field}={$previous_start_time}+)";

                        // run RETS search
                        echo "   + Query: {$query}  Limit: {$limit}  Offset: {$offset}<br>\n";
                        $search = $rets->Search("Property", "ResidentialProperty", "(ListPrice={$listPrice})", array("StandardNames" => 1, 'QueryType'=>'DMQL2', 'Limit' => $limit, 'Offset' => $offset, 'Format' => 'COMPACT-DECODED', 'Count' => 1));

        $count = count($search);

        echo "{$count}";

                
            foreach($search as $record){


        $photos = $rets->GetObject("Property", "Photo", $record['ListingId'], "0", 0);
        if ($photos !== null) {
         foreach ($photos as $photo) {

                $contentType = $photo->getContentType();
                $base64 = base64_encode($photo->getContent('Data')); 
                echo "<img src='data:{$contentType};base64,{$base64}' />";





         }
        }



        echo "<p>".$record->get('City').", $".$record->get('ListPrice').", ".$record->get('StreetNumber')." ".$record->get('StreetName').", ".$record->get('ListingId')."</p>";
                    
        }


        }
    }
?>