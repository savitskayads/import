<?php
header('Content-type: text/html; charset=utf-8');

$xmlPath='teleprogramm_mirhd.xml';
//настройки для подключения к бд
$user = 'root';
$password = 'root';
$dsn = 'mysql:dbname=mirhd;host=localhost;charset=UTF8';

$xml=simplexml_load_file($xmlPath);

if ($xml){

        foreach($xml as $row){

            foreach($row as $value) {

                foreach($value->attributes() as $fieldname=>$field){

                    $keyMirtv=(string)$field;
                    //формируем массив со значениями колонок
                    $valuesMirtv[$keyMirtv]=(string)$value;
                }

            }
            //формируем строку которая будет вставлена в sql запрос
            $allValuesMirtv=$allValuesMirtv."('".implode("','", $valuesMirtv)."'),";
        }
    $allValuesMirtv = substr($allValuesMirtv, 0, -1);
    //массив названий колонок из mirtv
    $columnsMirtv=array_keys($valuesMirtv);

//подключаемся к базе
    $opt = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    );

    $pdo = new PDO($dsn, $user, $password, $opt);
    //SHOW columns FROM teleprogramm_mirhd
    $columns="SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'mirhd' AND TABLE_NAME = 'teleprogramm_mirhd';";
    $stmt=$pdo->query($columns);
    //составляем массив названий колонок из mirhd
    $columnsMirhd=array();
    while ($row = $stmt->fetch())
    {
        foreach($row as $key){
            array_push($columnsMirhd,$key);
        }
    }

//проверяем одинаковые ли колонки в базах
    if (!array_diff($columnsMirhd, $columnsMirtv)){
        echo "Cтруктура таблиц совпадает";

        $sql= "INSERT INTO teleprogramm_mirhd (`" . implode("`, `",$columnsMirhd)."`) VALUES $allValuesMirtv;";

        $goData=$pdo->prepare($sql);
        $goData->execute();
        echo "<br> Данные отправлены в базу";
        }
    else{
        echo "<br> У таблиц разная структура! <br>";
        print_r(array_diff($columnsMirhd, $columnsMirtv));
    }
}
else{
    echo"Не найден xml файл";
}




