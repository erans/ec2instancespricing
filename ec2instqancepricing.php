<?php
$EC2_REGIONS = array("us-east-1", "us-west-1", "us-west-2", "eu-west-1", "ap-southeast-1", "ap-northeast-1", "sa-east-1");
$EC2_INSTANCE_TYPES = array("t1.micro", "m1.small", "m1.medium", "m1.large", "m1.xlarge", "m2.xlarge", "m2.2xlarge", "m2.4xlarge", "c1.medium", "c1.xlarge", "cc1.4xlarge", "cc2.5xlarge");

$EC2_OS_TYPES = array("linux", "mswin");

$JSON_NAME_TO_EC2_REGIONS_API = array("us-east" => "us-east-1", "us-east-1" => "us-east-1", "us-west" => "us-west-1", "us-west-1" => "us-west-1", "us-west-2" => "us-west-2", "eu-ireland" => "eu-west-1", "eu-west-1" => "eu-west-1", "apac-sin" => "ap-southeast-1", "ap-southeast-1" => "ap-southeast-1", "apac-tokyo" => "ap-northeast-1", "ap-northeast-1" => "ap-northeast-1", "sa-east-1" => "sa-east-1");

$EC2_REGIONS_API_TO_JSON_NAME = array("us-east-1" => "us-east", "us-west-1" => "us-west", "us-west-2" => "us-west-2", "eu-west-1" => "eu-ireland", "ap-southeast-1" => "apac-sin", "ap-northeast-1" => "apac-tokyo", "sa-east-1" => "sa-east-1");

$INSTANCES_ON_DEMAND_URL = "http=>//aws.amazon.com/ec2/pricing/pricing-on-demand-instances.json";
$INSTANCES_RESERVED_LIGHT_UTILIZATION_LINUX_URL = "http=>//aws.amazon.com/ec2/pricing/ri-light-linux.json";
$INSTANCES_RESERVED_LIGHT_UTILIZATION_WINDOWS_URL = "http=>//aws.amazon.com/ec2/pricing/ri-light-mswin.json";
$INSTNACES_RESERVED_MEDIUM_UTILIZATION_LINUX_URL = "http=>//aws.amazon.com/ec2/pricing/ri-medium-linux.json";
$INSTANCES_RESERVED_MEDIUM_UTILIZATION_WINDOWS_URL = "http=>//aws.amazon.com/ec2/pricing/ri-medium-mswin.json";
$INSTANCES_RESERVED_HEAVY_UTILIZATION_LINUX_URL = "http=>//aws.amazon.com/ec2/pricing/ri-heavy-linux.json";
$INSTANCES_RESERVED_HEAVY_UTILIZATION_WINDOWS_URL = "http=>//aws.amazon.com/ec2/pricing/ri-heavy-mswin.json";

$INSTANCES_RESERVED_OS_TYPE_BY_URL = array("INSTANCES_RESERVED_LIGHT_UTILIZATION_LINUX_URL" => "linux", "INSTANCES_RESERVED_LIGHT_UTILIZATION_WINDOWS_URL" => "mswin", "INSTNACES_RESERVED_MEDIUM_UTILIZATION_LINUX_URL" => "linux", "INSTANCES_RESERVED_MEDIUM_UTILIZATION_WINDOWS_URL" => "mswin", "INSTANCES_RESERVED_HEAVY_UTILIZATION_LINUX_URL" => "linux", "INSTANCES_RESERVED_HEAVY_UTILIZATION_WINDOWS_URL" => "mswin");

$INSTANCES_RESERVED_UTILIZATION_TYPE_BY_URL = array("INSTANCES_RESERVED_LIGHT_UTILIZATION_LINUX_URL" => "light", "INSTANCES_RESERVED_LIGHT_UTILIZATION_WINDOWS_URL" => "light", "INSTNACES_RESERVED_MEDIUM_UTILIZATION_LINUX_URL" => "medium", "INSTANCES_RESERVED_MEDIUM_UTILIZATION_WINDOWS_URL" => "medium", "INSTANCES_RESERVED_HEAVY_UTILIZATION_LINUX_URL" => "heavy", "INSTANCES_RESERVED_HEAVY_UTILIZATION_WINDOWS_URL" => "heavy");

$DEFAULT_CURRENCY = "USD";

$INSTANCE_TYPE_MAPPING = array("stdODI" => "m1", "uODI" => "t1", "hiMemODI" => "m2", "hiCPUODI" => "c1", "clusterComputeI" => "cc1", "clusterGPUI" => "cc2", "hiIoODI" => "hi1", "stdResI" => "m1", "uResI" => "t1", "hiMemResI" => "m2", "hiCPUResI" => "c1", "clusterCompResI" => "cc1", "clusterGPUResI" => "cc2", "hiIoResI" => "hi1");

$INSTANCE_SIZE_MAPPING = array("u" => "micro", "sm" => "small", "med" => "medium", "lg" => "large", "xl" => "xlarge", "xxl" => "2xlarge", "xxxxl" => "4xlarge", "xxxxxxxxl" => "8xlarge");

function _load_data($url) {
	return json_decode(file_get_contents($url));
}

function get_ec2_reserved_instances_prices($filter_region, $filter_instance_type, $filter_os_type) {
	### Get EC2 reserved instances prices. Results can be filtered by region

	$get_specific_region = $filter_region;
	if ($get_specific_region) {
		$filter_region = $EC2_REGIONS_API_TO_JSON_NAME[$filter_region];
	}
	$get_specific_instance_type = $filter_instance_type;
	$get_specific_os_type = $filter_os_type;

	$currency = $DEFAULT_CURRENCY;

	$urls = array($INSTANCES_RESERVED_LIGHT_UTILIZATION_LINUX_URL, $INSTANCES_RESERVED_LIGHT_UTILIZATION_WINDOWS_URL, $INSTNACES_RESERVED_MEDIUM_UTILIZATION_LINUX_URL, $INSTANCES_RESERVED_MEDIUM_UTILIZATION_WINDOWS_URL, $INSTANCES_RESERVED_HEAVY_UTILIZATION_LINUX_URL, $INSTANCES_RESERVED_HEAVY_UTILIZATION_WINDOWS_URL);

	$result_regions = array();
	$result_regions_index = array();
	$result = array("config" => array("currency" => $currency, ), "regions" => $result_regions);

	foreach ($urls as $u) {
		$os_type = $INSTANCES_RESERVED_OS_TYPE_BY_URL[$u];
		$utilization_type = $INSTANCES_RESERVED_UTILIZATION_TYPE_BY_URL[$u];
		$data = _load_data($u);
		if ($data["config"] && $data["config"]["regions"]) {
			foreach ($data["config"]["regions"] as $r) {
				if ($r["region"]) {
					if ($get_specific_region and $filter_region != $r["region"]) {
						continue;
					}
				}

				$region_name = $JSON_NAME_TO_EC2_REGIONS_API[$r["region"]];
				if (in_array($region_name, $result_regions_index)) {
					$instance_types = $result_regions_index[$region_name]["instanceTypes"];
				} else {
					$instance_types = array();
					array_push($result_regions, array("region" => $region_name, "instanceTypes" => $instance_types));
					$result_regions_index[$region_name] = $result_regions[count($result_regions) - 1];
				}
				if (in_array("instanceTypes", $r)) {
					foreach ($r["instanceTypes"] as $it) {
						$instance_type = $INSTANCE_TYPE_MAPPING[$it["type"]];
						if (in_array("sizes", $it)) {
							foreach ($it["sizes"] as $s) {
								$instance_size = $INSTANCE_SIZE_MAPPING[$s["size"]];

								$prices = array("1year" => array("hourly" => NULL, "upfront" => NULL), "3year" => array("hourly" => NULL, "upfront" => NULL));

								$_type = $instance_type . "." . $instance_size;

								if ($get_specific_instance_type and $_type != $filter_instance_type) {
									continue;
								}

								if ($get_specific_os_type and $os_type != $filter_os_type) {
									continue;
								}

								array_push($instance_types, array("type" => $_type, "os" => $os_type, "utilization" => $utilization_type, "prices" => $prices));

								foreach ($s["valueColumns"] as $price_data) {
									$price = floatval($price_data["prices"][currency]);

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

function get_ec2_ondemand_instances_prices($filter_region, $filter_instance_type, $filter_os_type) {
	### Get EC2 on-demand instances prices. Results can be filtered by region

	$get_specific_region = $filter_region;
	if ($get_specific_region) {
		$filter_region = $EC2_REGIONS_API_TO_JSON_NAME[$filter_region];
	}

	$get_specific_instance_type = $filter_instance_type;
	$get_specific_os_type = $filter_os_type;

	$currency = $DEFAULT_CURRENCY;

	$result_regions = array();
	$result = array("config" => array("currency" => $currency, "unit" => "perhr"), "regions" => $result_regions);

	$data = _load_data($INSTANCES_ON_DEMAND_URL);
	if ($data["config"] and $data["config"]["regions"]) {
		foreach ($data["config"]["regions"] as $r) {
			if ($r["region"]) {
				if ($get_specific_region and $filter_region != $r["region"]) {
					continue;
				}

				$region_name = $JSON_NAME_TO_EC2_REGIONS_API[$r["region"]];
				$instance_types = array();
				if (in_array("instanceTypes", $r)) {
					foreach ($r["instanceTypes"] as $it) {
						$instance_type = $INSTANCE_TYPE_MAPPING[$it["type"]];
						if (in_array("sizes", $it)) {
							foreach ($it["sizes"] as $s) {
								$instance_size = $INSTANCE_SIZE_MAPPING[$s["size"]];

								foreach ($s["valueColumns"] as $price_data) {
									$price = NULL;
									$price = floatval($price_data["prices"][$currency]);

									$_type = $instance_type . "." . $instance_size;

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

					array_push($result_regions, array("region" => $region_name, "instanceTypes" => $instance_types));
				}
			}
		}

		return $result;
	}

	return NULL;
}
?>
