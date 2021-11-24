<?php
include '../system/autoload.php';
class User_Model extends Model
{
    private $db;
    public $site_url;
    public function __construct()
    {
        parent::__construct();
        $this->db = new Database();
        $this->site_url = $this->server->site_url();
    }
    public function fetch_data() {
        try {
            $this->db->where(["salary" => 0], ">");
            $this->db->orderBy("name");
            $data = $this->db->getValues("id, name, position, office, age, start_date, salary", "employees");
        } catch (Exception $e) {
        }
        return $data;
    }

    public function get_statistics() {
        return $this->stat();
    }
    private function stat()
    {
        $this->db->where(["salary" => 0], ">");
        $min_salary = $this->db->get_min_value("salary", "employees");
        $max_salary = $this->db->get_max_value("salary", "employees");
        try {
            $av_sal = $this->db->customQuery("SELECT AVG(salary) FROM employees");
            $av_age = $this->db->customQuery("SELECT AVG(age) FROM employees");
            foreach ($av_sal as $key => $value) {
                $average_salary = $value["AVG(salary)"];
            }
            foreach ($av_age as $key => $value) {
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
        return array(
            "min_salary" => number_format($min_salary),
            "max_salary" => number_format($max_salary),
            "agv_salary" => number_format(ceil($average_salary)),
            "min_age"   => $min_age,
            "max_age"   => $max_age,
            "most_payed" => $most_payed->name,
            "least_payed" => $least_payed->name,
            "youngest"   => $youngest_emp->name,
            "eldest"     => $eldest_emp->name,
            "avg_age"    => ceil($average_age)
        );
    }
    public function get_graph() {
        return $this->age_distribution();
    }

    private function age_distribution(){
        $this->db->where(["salary" => 0], ">");
        $data = $this->db->getValues("name, age, salary", "employees");
        $dataPoints = array();
        foreach ($data as $key => $emp) {
            array_push($dataPoints, array("x"=> $emp["salary"], "y"=> $emp["age"], "indexLabel"=> $emp["name"]));
//                array("y" => $emp["salary"], "label" => $emp["name"] )
        }
        return $dataPoints;

    }

    public  function salary_distribution() {
        $this->db->where(["salary" => 0], ">");
        $data = $this->db->getValues("name, age, salary", "employees");
        $dataPoints = array();
        foreach ($data as $key => $emp) {
            array_push($dataPoints, array("y" => $emp["salary"], "label" => $emp["name"] ));

        }
        return $dataPoints;

    }
}
