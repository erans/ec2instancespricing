<?php
/**
 * EC2InstancePrices
 *
 * The EC2InstancePrices class exposes the 'get_ec2_reserved_instances_prices' and the 'get_ec2_ondemand_instances_prices' methods
 * which return the price data from Amazom AWS in an easy to use format
 *
 * It also exposes the 'get_ec2_data' method which prints out the modified-data as JSON
 *
 * @author Sathvik P, Doers' Guild
 * Based on the python version by Eran Sandler
 *
 */
class EC2InstancePrices {
	private $EC2_REGIONS = array("us-east-1", "us-west-1", "us-west-2", "eu-west-1", "ap-southeast-1", "ap-southeast-2", "ap-northeast-1", "sa-east-1");
	private $EC2_INSTANCE_TYPES = array("t1.micro", "m1.small", "m1.medium", "m1.large", "m1.xlarge", "m2.xlarge", "m2.2xlarge", "m3.xlarge", "m3.2xlarge", "m2.4xlarge", "c1.medium", "c1.xlarge", "cc1.4xlarge", "cc2.8xlarge", "cg1.4xlarge");

	private $EC2_OS_TYPES = array("linux", "mswin");

	private $JSON_NAME_TO_EC2_REGIONS_API = array("us-east" => "us-east-1", "us-east-1" => "us-east-1", "us-west" => "us-west-1", "us-west-1" => "us-west-1", "us-west-2" => "us-west-2", "eu-ireland" => "eu-west-1", "eu-west-1" => "eu-west-1", "apac-sin" => "ap-southeast-1", "ap-southeast-1" => "ap-southeast-1", "ap-southeast-2" => "ap-southeast-2", "apac-syd" => "ap-southeast-2", "apac-tokyo" => "ap-northeast-1", "ap-northeast-1" => "ap-northeast-1", "sa-east-1" => "sa-east-1");

	private $EC2_REGIONS_API_TO_JSON_NAME = array("us-east-1" => "us-east", "us-west-1" => "us-west", "us-west-2" => "us-west-2", "eu-west-1" => "eu-ireland", "ap-southeast-1" => "apac-sin", "ap-southeast-2" => "apac-syd", "ap-northeast-1" => "apac-tokyo", "sa-east-1" => "sa-east-1");

	private $INSTANCES_ON_DEMAND_URL = "http://aws.amazon.com/ec2/pricing/pricing-on-demand-instances.json";
	private $INSTANCES_RESERVED_LIGHT_UTILIZATION_LINUX_URL = "http://aws.amazon.com/ec2/pricing/ri-light-linux.json";
	private $INSTANCES_RESERVED_LIGHT_UTILIZATION_WINDOWS_URL = "http://aws.amazon.com/ec2/pricing/ri-light-mswin.json";
	private $INSTNACES_RESERVED_MEDIUM_UTILIZATION_LINUX_URL = "http://aws.amazon.com/ec2/pricing/ri-medium-linux.json";
	private $INSTANCES_RESERVED_MEDIUM_UTILIZATION_WINDOWS_URL = "http://aws.amazon.com/ec2/pricing/ri-medium-mswin.json";
	private $INSTANCES_RESERVED_HEAVY_UTILIZATION_LINUX_URL = "http://aws.amazon.com/ec2/pricing/ri-heavy-linux.json";
	private $INSTANCES_RESERVED_HEAVY_UTILIZATION_WINDOWS_URL = "http://aws.amazon.com/ec2/pricing/ri-heavy-mswin.json";

	private $INSTANCES_RESERVED_OS_TYPE_BY_URL = array();

	private $INSTANCES_RESERVED_UTILIZATION_TYPE_BY_URL = array();

	private $DEFAULT_CURRENCY = "USD";

	private $INSTANCE_TYPE_MAPPING = array("stdODI" => "m1", "uODI" => "t1", "hiMemODI" => "m2", "hiCPUODI" => "c1", "clusterComputeI" => "cc1", "hiIoODI" => "hi1", "stdResI" => "m1", "uResI" => "t1", "hiMemResI" => "m2", "hiCPUResI" => "c1", "clusterCompResI" => "cc1", "hiIoResI" => "hi1", "clusterGPUResI" => "cg1", "clusterGPUI" => "cg1", "secgenstdResI" => "m3", "secgenstdODI" => "m3");

	private $INSTANCE_SIZE_MAPPING = array("u" => "micro", "sm" => "small", "med" => "medium", "lg" => "large", "xl" => "xlarge", "xxl" => "2xlarge", "xxxxl" => "4xlarge", "xxxxxxxxl" => "8xlarge");

	private $EC2_USER_OS = array("linux" => "Linux/Unix", "mswin" => "Windows");

	function __construct() {

		$this -> INSTANCES_RESERVED_OS_TYPE_BY_URL[$this -> INSTANCES_RESERVED_LIGHT_UTILIZATION_LINUX_URL] = "linux";
		$this -> INSTANCES_RESERVED_OS_TYPE_BY_URL[$this -> INSTANCES_RESERVED_LIGHT_UTILIZATION_WINDOWS_URL] = "mswin";
		$this -> INSTANCES_RESERVED_OS_TYPE_BY_URL[$this -> INSTNACES_RESERVED_MEDIUM_UTILIZATION_LINUX_URL] = "linux";
		$this -> INSTANCES_RESERVED_OS_TYPE_BY_URL[$this -> INSTANCES_RESERVED_MEDIUM_UTILIZATION_WINDOWS_URL] = "mswin";
		$this -> INSTANCES_RESERVED_OS_TYPE_BY_URL[$this -> INSTANCES_RESERVED_HEAVY_UTILIZATION_LINUX_URL] = "linux";
		$this -> INSTANCES_RESERVED_OS_TYPE_BY_URL[$this -> INSTANCES_RESERVED_HEAVY_UTILIZATION_WINDOWS_URL] = "mswin";

		$this -> INSTANCES_RESERVED_UTILIZATION_TYPE_BY_URL[$this -> INSTANCES_RESERVED_LIGHT_UTILIZATION_LINUX_URL] = "light";
		$this -> INSTANCES_RESERVED_UTILIZATION_TYPE_BY_URL[$this -> INSTANCES_RESERVED_LIGHT_UTILIZATION_WINDOWS_URL] = "light";
		$this -> INSTANCES_RESERVED_UTILIZATION_TYPE_BY_URL[$this -> INSTNACES_RESERVED_MEDIUM_UTILIZATION_LINUX_URL] = "medium";
		$this -> INSTANCES_RESERVED_UTILIZATION_TYPE_BY_URL[$this -> INSTANCES_RESERVED_MEDIUM_UTILIZATION_WINDOWS_URL] = "medium";
		$this -> INSTANCES_RESERVED_UTILIZATION_TYPE_BY_URL[$this -> INSTANCES_RESERVED_HEAVY_UTILIZATION_LINUX_URL] = "heavy";
		$this -> INSTANCES_RESERVED_UTILIZATION_TYPE_BY_URL[$this -> INSTANCES_RESERVED_HEAVY_UTILIZATION_WINDOWS_URL] = "heavy";

	}

	function _load_data($url) {
		return json_decode(file_get_contents($url), TRUE);
	}

	function get_ec2_reserved_instances_prices($filter_region = NULL, $filter_instance_type = NULL, $filter_os_type = NULL) {
		### Get EC2 reserved instances prices. Results can be filtered by region

		$get_specific_region = $filter_region;
		if ($get_specific_region) {
			$filter_region = $this -> EC2_REGIONS_API_TO_JSON_NAME[$filter_region];
		}
		$get_specific_instance_type = $filter_instance_type;
		$get_specific_os_type = $filter_os_type;

		$currency = $this -> DEFAULT_CURRENCY;

		$urls = array($this -> INSTANCES_RESERVED_LIGHT_UTILIZATION_LINUX_URL, $this -> INSTANCES_RESERVED_LIGHT_UTILIZATION_WINDOWS_URL, $this -> INSTNACES_RESERVED_MEDIUM_UTILIZATION_LINUX_URL, $this -> INSTANCES_RESERVED_MEDIUM_UTILIZATION_WINDOWS_URL, $this -> INSTANCES_RESERVED_HEAVY_UTILIZATION_LINUX_URL, $this -> INSTANCES_RESERVED_HEAVY_UTILIZATION_WINDOWS_URL);

		$result_regions_index = array();
		$result_indices = array();
		$result = array("config" => array("currency" => $currency, ), "regions" => array());

		foreach ($urls as $u) {
			$os_type = $this -> INSTANCES_RESERVED_OS_TYPE_BY_URL[$u];
			$utilization_type = $this -> INSTANCES_RESERVED_UTILIZATION_TYPE_BY_URL[$u];
			$data = $this -> _load_data($u);
			if ($data["config"] && $data["config"]["regions"]) {
				foreach ($data["config"]["regions"] as $r) {
					if ($r["region"]) {
						if ($get_specific_region and $filter_region != $r["region"]) {
							continue;
						}
					}

					$region_name = $this -> JSON_NAME_TO_EC2_REGIONS_API[$r["region"]];
					if (array_key_exists($region_name, $result_regions_index)) {
						$instance_types = $result["regions"][$result_indices[$region_name]]["instanceTypes"];
					} else {
						$instance_types = array();
						$result_indices[$region_name] = array_push($result["regions"], array("region" => $region_name, "instanceTypes" => $instance_types)) - 1;
						$result_regions_index[$region_name] = $result["regions"][count($result["regions"]) - 1]["region"];
					}
					if (array_key_exists("instanceTypes", $r)) {
						foreach ($r["instanceTypes"] as $it) {
							$instance_type = $this -> INSTANCE_TYPE_MAPPING[$it["type"]];
							if (array_key_exists("sizes", $it)) {
								foreach ($it["sizes"] as $s) {
									$instance_size = $this -> INSTANCE_SIZE_MAPPING[$s["size"]];

									$prices = array("1year" => array("hourly" => NULL, "upfront" => NULL), "3year" => array("hourly" => NULL, "upfront" => NULL));

									$_type = $instance_type . "." . $instance_size;
									if ($_type == "cc1.8xlarge") {
										//Fix conflict where cc1 and cc2 share the same type
										$_type = "cc2.8xlarge";
									}

									if ($get_specific_instance_type and $_type != $filter_instance_type) {
										continue;
									}

									if ($get_specific_os_type and $os_type != $filter_os_type) {
										continue;
									}

									array_push($result["regions"][$result_indices[$region_name]]["instanceTypes"], array("type" => $_type, "os" => $os_type, "utilization" => $utilization_type, "prices" => $prices));

									foreach ($s["valueColumns"] as $price_data) {
										$price = floatval($price_data["prices"][$currency]);

										if ($price_data["name"] == "yrTerm1")
											$prices["1year"]["upfront"] = $price;
										elseif ($price_data["name"] == "yrTerm1Hourly")
											$prices["1year"]["hourly"] = $price;
										elseif ($price_data["name"] == "yrTerm3")
											$prices["3year"]["upfront"] = $price;
										elseif ($price_data["name"] == "yrTerm3Hourly")
											$prices["3year"]["hourly"] = $price;
									}
								}
							}
						}
					}
				}
			}
		}

		return $result;
	}

	function get_ec2_ondemand_instances_prices($filter_region = NULL, $filter_instance_type = NULL, $filter_os_type = NULL) {
		### Get EC2 on-demand instances prices. Results can be filtered by region

		$get_specific_region = $filter_region;
		if ($get_specific_region) {
			$filter_region = $this -> EC2_REGIONS_API_TO_JSON_NAME[$filter_region];
		}

		$get_specific_instance_type = $filter_instance_type;
		$get_specific_os_type = $filter_os_type;

		$currency = $this -> DEFAULT_CURRENCY;

		$result = array("config" => array("currency" => $currency, "unit" => "perhr"), "regions" => array());

		$data = $this -> _load_data($this -> INSTANCES_ON_DEMAND_URL);
		if ($data["config"] and $data["config"]["regions"]) {
			foreach ($data["config"]["regions"] as $r) {
				if ($r["region"]) {
					if ($get_specific_region and $filter_region != $r["region"]) {
						continue;
					}

					$region_name = $this -> JSON_NAME_TO_EC2_REGIONS_API[$r["region"]];
					$instance_types = array();
					if (array_key_exists("instanceTypes", $r)) {
						foreach ($r["instanceTypes"] as $it) {
							$instance_type = $this -> INSTANCE_TYPE_MAPPING[$it["type"]];
							if (array_key_exists("sizes", $it)) {
								foreach ($it["sizes"] as $s) {
									$instance_size = $this -> INSTANCE_SIZE_MAPPING[$s["size"]];

									foreach ($s["valueColumns"] as $price_data) {
										$price = NULL;
										$price = floatval($price_data["prices"][$currency]);

										$_type = $instance_type . "." . $instance_size;
										if ($_type == "cc1.8xlarge") {
											//Fix conflict where cc1 and cc2 share the same type
											$_type = "cc2.8xlarge";
										}

										if ($get_specific_instance_type and $_type != $filter_instance_type) {
											continue;
										}

										if ($get_specific_os_type and $price_data["name"] != $filter_os_type) {
											continue;
										}

										array_push($instance_types, array("type" => $_type, "os" => $price_data["name"], "price" => $price));
									}
								}
							}
						}

						array_push($result["regions"], array("region" => $region_name, "instanceTypes" => $instance_types));
					}
				}
			}

			return $result;
		}

		return NULL;
	}

	function get_ec2_data() {

		$ret = array("reserved_instances" => $this -> get_ec2_reserved_instances_prices(), "ondemand" => $this -> get_ec2_ondemand_instances_prices());

		header('Content-type: application/json');
		print json_encode($ret);
	}

}
?>
<?php
/* Sample usage:
 $ec2 = new EC2InstancePrices();
 print_r($ec2 -> get_ec2_reserved_instances_prices());
 echo "\n<br/>=================================================\n<br/>";
 print_r($ec2 -> get_ec2_ondemand_instances_prices());
 echo "\n<br/>=================================================\n<br/>";
 //$ec2 -> get_ec2_data();
 */
?>