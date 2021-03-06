<?php

/**
 * @category  Table Scraping
 * @package   Scrapping
 * @author Tumuhimbise Usher Godwin
 * @copyright Copyright (c) 2021
 * @license GPL
 * @version   v1.0
 */
class Scrapping
{
    public static $config;
    private static $url;
    private static $columns = array();
    private static $table_name;
    public function __construct()
    {

    }

    /**
     * @param string $host The host server name, Default = localhost
     * @param string $user The username of the connected user, Default = root
     * @param string $password The password of the connected account, Default = ""
     * @param string $database The database name to connect to.
     * Should be created first
     */
    public static function config(string $host="localhost", string $user = "root", string $password = "", string $database = "") {
        self::$config[] = array(
            "HOST" => $host,
            "USER" => $user,
            "PASSWORD" => $password,
            "DB" => $database);
    }

    /**
     * @param string $url The url where to scrap data from
     */
    public static function url(string $url) {
        self::$url = $url;
    }

    /**
     * @param array $column_names The name of table columns in the table where to scrap data from
     * The column names are used when saving data in a database.
     */

    public static function tableColumns(array $column_names) {
        self::$columns = $column_names;
    }

    /**
     * @param bool $save_data Set it to True if you want to save the scraped data in the database
     * @param string $table_name The name of the table to insert data to
     */
    public static function startScraping(bool $save_data = false, string $table_name = ""){
        self::$table_name = $table_name;
        self::begin($save_data, $table_name);
    }

    /**
     * @param $save bool Set it to true if you want to save the scraped data in the database
     * @param $table string The name of the table to insert the table in
     * Should be created first.
     */
    private static function begin(bool $save, string $table) {
        $curl = curl_init();
        $url = self::$url;
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_REFERER, $url);
        $XHTML = curl_exec($curl);
        curl_close($curl);
        $dom = new DOMDocument();
        @$dom->loadHTML($XHTML);
        $xpath = new DOMXPath($dom);
        $data = array();
        foreach ($xpath->query("//table")->item(0)->getElementsByTagName('tr')
        as $table_rows) {
            $table_data = $table_rows->getElementsByTagName('td');
            /**
             * @var $data
             * Change the array keys to match the column names of the table that you are scraping.
             * Change the index int item() to match the index of table row that you are scraping
             */
            $data[] = array(
                "name" => $table_data->item(1)->nodeValue."<br/>",
                "games" => $table_data->item(2)->textContent,
                "goals" => $table_data->item(3)->textContent
            );
        }
        // print_r($data);
        if ($save) {
           self::saveDataToDatabase($data, $table);
        }
    }

    /**
     * @param array $data The data to insert in the database
     * @param string $table Table name
     * @returns void
     */

    private static function saveDataToDatabase(array $data, string $table) {
        $db = self::connect();
        $sql = "INSERT INTO ".$table." (".implode(", ", self::$columns).") VALUES (";
        $place_holders = array();
        for ($cl = 0; $cl <= count(self::$columns) - 1; $cl++) {
            array_push($place_holders, ":".self::$columns[$cl]);
        }
        $holders = implode(", ", $place_holders);
        $sql .= $holders.")";
    
        $db->beginTransaction();
        $stmt = $db->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->execute($value);
        }
        $db->commit();

    }

    /**
     * Do not edit this method
     * @return PDO
     */

    private static function connect() {
        $host = "";
        $user = "";
        $pass = "";
        $db = "";
        foreach (self::$config as $key => $value) {
            $host = $value["HOST"];
            $user = $value["USER"];
            $pass = $value["PASSWORD"];
            $db = $value["DB"];
        }
        try {
            $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (PDOException $e) {
            echo $e->getMessage();
        }
        return $conn;
    }

    /**
     * Fetches all the data the was previously scraped and saved in the database
     * @param string $table_name
     * @return array
     */
    public static function getData(string $table_name) {
        $sql = "SELECT * FROM ".$table_name;
        $db = self::connect();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function topScorer()
    {
        # code...
        $sql = "SELECT MAX(goals) FROM epl_stats";
        $db = self::connect();
        $db->beginTransaction();
        $stmt = $db->prepare($sql);
        $stmt -> setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        $db->commit();
        $data = $stmt->fetch();
        $top = $data['MAX(goals)'];
        $sql2 = "SELECT `name` FROM epl_stats WHERE goals = ?";
        $db->beginTransaction();
        $stmt = $db->prepare($sql2);
        $stmt->bindParam(1, $top, PDO::PARAM_STR);
        $stmt->execute();
        $stmt -> setFetchMode(PDO::FETCH_ASSOC);
        $name = $stmt->fetch();
        $db->commit();
        if (!empty($name))
        return $name['name'];

    }
}
/**
 * Set the database configurations
 */

Scrapping::config("localhost", "root", "", "integrative_programming");
/**
 * Set the url where to scrap data from
 */
Scrapping::url("https://sports.ndtv.com/english-premier-league/stats/top-goal-scorers-player-statsdetail/2020-21");
/**
 * Pass the column names of the table you are scraping
 */
Scrapping::tableColumns(
    ["name",
    "games",
    "goals"
    ]);

/**
 * Start scraping when a request is made
 * The request string should match with the name parsed in ajax
 */
if (isset($_REQUEST["start"]))
Scrapping::startScraping(true, "epl_stats");
