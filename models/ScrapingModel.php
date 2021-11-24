<?php
include_once '../system/autoload.php';
/**
 * @class ScrapingModel
 * @uthor Tumuhimbise Godwin 
 * 18/u/22816/ps 
 * 1800722816
 */
class ScrapingModel extends Model
{
    private static $url = "";
    private static $tags = array();
    private $db;
    public function __construct()
    {
        parent::__construct();
        $this->db = new Database();
    }

    public static function url(string $url)
    {
        self::$url = $url;
    }
    public function scrap(bool $save_to_db = false)
    {
        try {
            $data = $this->db->getValues("name, age", "employees");
        } catch (Exception $e) {
        }
        if (empty($data)) {
            $this->start($save_to_db);
        }else {
            return false;
        }
    }

    private function start(bool $save)
    {
        $ch = curl_init();
        $url = self::$url;
        $base = $url;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_REFERER, $base);
        $html = curl_exec($ch);
        curl_close($ch);
        $doc = new DOMDocument();
        // It's rare you'll have valid XHTML, suppress any errors- it'll do its best.
        $doc->loadhtml($html);

        $xpath = new DOMXPath($doc);
        // Modify the XPath query to match the content
        foreach ($xpath->query('//table')->item(0)->getElementsByTagName('tr') as $rows) {
            $cells = $rows->getElementsByTagName('td');

            // Do stuff with the data
            $data = array();
            $data[] = array(
                "name" => $cells->item(0)->textContent,
                "position" => $cells->item(1)->textContent,
                "office" => $cells->item(2)->textContent,
                "age" => $cells->item(3)->textContent,
                "start_date" => $cells->item(4)->textContent,
                "salary" => (int)trim(str_replace(["$",","], "", $cells->item(5)->textContent))
            );
            if ($save) {
                foreach ($data as $key => $value){
                    $scraped = [
                        "name" => $value["name"],
                        "position" => $value["position"],
                        "office" => $value["office"],
                        "age" => $value["age"],
                        "start_date" => $value["start_date"],
                        "salary" => $value["salary"]
                        ];
                    $this->db->insert("employees", $scraped);
                }
            }
        }

        }
        public function fetch_data() {
            try {
                $this->db->where(["salary" => 0], ">");
                $this->db->orderBy("name");
                $data = $this->db->getValues("id, name as name, position, office, age, start_date, salary", "employees", "10");
                if (!empty($data)) {
                    $this->generate_table($data);
                }
            } catch (Exception $e) {
            }

        }
        private function generate_table($data) {
        echo "<table class='table table-striped table-bordered shadow' id='table1'>
            <thead>
            <tr>
            <th>NO</th>
            <th>NAME</th>
            <th>POSITION</th>
            <th>OFFICE</th>
            <th>AGE</th>
            <th>START DATE</th>
            <th>SALARY</th>
           </tr>
           </thead>";

        $i = 0;
        foreach ($data as $key => $n) {
            echo "<tbody><tr>";
                if(!empty($n))
                {
                    $i++;
                    echo "<td>".$i."</td>";
                    echo "<td>".$n['name']."</td>";
                    echo "<td>".$n['position']."</td>";
                    echo "<td>".$n['office']."</td>";
                    echo "<td>".$n['age']."</td>";
                    echo "<td>".$n['start_date']."</td>";
                    echo "<td>"."$".$n['salary']."</td>";
                }
            echo "</tr></tbody>";
        }
        echo "
            <tfoot>
            <tr>
            <th>NO</th>
            <th>NAME</th>
            <th>POSITION</th>
            <th>OFFICE</th>
            <th>AGE</th>
            <th>START DATE</th>
            <th>SALARY</th>
           </tr>
           </tfoot>
            </table>";
        }

        public function get_statistics() {
        $this->stat();
        }
        private function stat(){
        $this->db->where(["salary" => 0], ">");
        $min_salary = $this->db->get_min_value("salary", "employees");
        $max_salary = $this->db->get_max_value("salary", "employees");
            try {
                $av_sal = $this->db->customQuery("SELECT AVG(salary) FROM employees");
                $av_age = $this->db->customQuery("SELECT AVG(age) FROM employees");
                foreach ( $av_sal as $key => $value) {
                    $average_salary = $value["AVG(salary)"];
                }
                foreach ($av_age as $key => $value){
                    $average_age = $value["AVG(age)"];
                }
            } catch (Exception $e) {
            }
            $this->db->where(["salary" => $min_salary]);
            try {
                $least_payed = $this->db->getObject("name", "employees");
            } catch (Exception $e) {
            }
            $this->db->where(["salary" => $max_salary]);
            try {
                $most_payed = $this->db->getObject("name", "employees");
            } catch (Exception $e) {
            }
            $min_age = $this->db->get_min_value("age", "employees");
            $max_age = $this->db->get_max_value("age", "employees");
            $this->db->where(["age" => $min_age]);
            try {
                $youngest_emp = $this->db->getObject("name", "employees");
            } catch (Exception $e) {
            }
            $this->db->where(["age" => $max_age]);
            try {
                $eldest_emp = $this->db->getObject("name", "employees");
            } catch (Exception $e) {
            }
            echo "
            <div class='bg-light text-dark'>
            <div class='row justify-content-between'>
            <h5>MAXIMUM SALARY: </h5><h5 class='text-success'>$".number_format($max_salary)."</h5>
            </div><hr/>
            <div class='row justify-content-between'>
            <h5>MINIMUM SALARY: </h5><h5 class='text-success'>$".number_format($min_salary)."</h5>
            </div><hr/>
            <div class='row justify-content-between'>
            <h5>AVERAGE SALARY: </h5> <h5 class='text-success'>$".number_format(ceil($average_salary))."</h5>
            </div><hr/>
            <div class='row justify-content-between'>
            <h5>HIGHLY EARNING EMPLOYEE: </h5> <h5 class='text-success'>".$most_payed->name."</h5>
            </div><hr/>
            <div class='row justify-content-between'>
            <h5>LEAST EARNING EMPLOYEE: </h5> <h5 class='text-success'>".$least_payed->name."</h5>
            </div><hr/>
            <div class='row justify-content-between'>
            <h5>YOUNGEST EMPLOYEE:</h5> <h5 class='text-success'>".$youngest_emp->name."</h5>
            </div><hr/>
            <div class='row justify-content-between'>
            <h5>ELDEST EMPLOYEE:</h5> <h5 class='text-success'>".$eldest_emp->name."</h5>
            </div><hr/>
            <div class='row justify-content-between'>
            <h5>AVERAGE EMPLOYEE AGE:</h5> <h5 class='text-success'>".ceil($average_age)."</h5>
            </div>
            <hr/>
            <a href='../app/statistics.php' class='btn btn-outline-success'>View Expanded Details</a>
            </div>
            ";
        }

}

$scrap = new ScrapingModel();
if (isset($_REQUEST["get"])) {
    $url = "https://datatables.net/examples/styling/bootstrap4";
    ScrapingModel::url($url);
    $scrap->scrap(true);

}

if (isset($_REQUEST["table"])) {
    $scrap->fetch_data();
}

if (isset($_REQUEST['get_stat'])) {
    $scrap->get_statistics();
}
