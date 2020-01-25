<?php

namespace HemiFrame\Lib;

/**
 * @author heminei <heminei@heminei.com>
 */
class GeoIp
{

	private $ip;
	private $databaseInfo;
	private $continentCode;
	private $countryCode;
	private $countryCode3;
	private $countryName;
	private $region;
	private $city;
	private $postalCode;
	private $latitude;
	private $longitude;
	private $dmaCode;
	private $areaCode;
	private $timeZone = NULL;

	public function __construct($ip = NULL)
	{
		if (empty($ip)) {
			throw new \Exception("Enter ip.");
		}
		$this->ip = $ip;

		if (!function_exists("geoip_database_info")) {
			throw new \Exception("GeoIp not installed.");
		}
		$this->databaseInfo = \geoip_database_info();

		$record = \geoip_record_by_name($ip);

		if (is_array($record)) {
			$this->continentCode = $record['continent_code'];
			$this->countryCode = $record['country_code'];
			$this->countryCode3 = $record['country_code3'];
			$this->countryName = $record['country_name'];
			$this->region = $record['region'];
			$this->city = $record['city'];
			$this->postalCode = $record['postal_code'];
			$this->latitude = $record['latitude'];
			$this->longitude = $record['longitude'];
			$this->dmaCode = $record['dma_code'];
			$this->areaCode = $record['area_code'];
		}

		if (!empty($this->getCountryCode())) {
			$this->timeZone = \geoip_time_zone_by_country_and_region($this->getCountryCode());
		}
	}

	public function getIp(): ?string
	{
		return $this->ip;
	}

	public function getDatabaseInfo(): ?string
	{
		return $this->databaseInfo;
	}

	public function getContinentCode(): ?string
	{
		return $this->continentCode;
	}

	public function getCountryCode(): ?string
	{
		return $this->countryCode;
	}

	public function getCountryCode3(): ?string
	{
		return $this->countryCode3;
	}

	public function getCountryName(): ?string
	{
		return $this->countryName;
	}

	public function getRegion(): ?string
	{
		return $this->region;
	}

	public function getCity(): ?string
	{
		return $this->city;
	}

	public function getPostalCode(): ?string
	{
		return $this->postalCode;
	}

	public function getLatitude(): float
	{
		return $this->latitude;
	}

	public function getLongitude(): float
	{
		return $this->longitude;
	}

	public function getDmaCode()
	{
		return $this->dmaCode;
	}

	public function getAreaCode()
	{
		return $this->areaCode;
	}

	public function getTimeZone()
	{
		return $this->timeZone;
	}
}
