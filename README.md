<p align="center">
  <a href="http://www.sphdigital.com/" target="_blank" >
    <img alt="SPH Digital" src="http://www.sphdigital.com/wp-content/uploads/2015/04/logo_sphdd.svg" width="400" />
  </a>
</p>
<p align="center">
  <a href="LICENSE" target="_blank">
    <img alt="Software License" src="https://img.shields.io/badge/License-GPL%20v3-blue.svg">
  </a>
  <a href="https://packagist.org/packages/sphdd/authorized_digital_sellers" target="_blank">
    <img alt="Total Downloads" src="https://img.shields.io/packagist/dt/sphdd/authorized_digital_sellers.svg">
  </a>
  <a href="https://packagist.org/packages/sphdd/authorized_digital_sellers" target="_blank">
    <img alt="Latest Stable Version" src="https://img.shields.io/packagist/v/sphdd/authorized_digital_sellers.svg">
  </a>
</p>
# Authorized Digital Sellers Manager Tool

ADS.TXT manager for Drupal 8 is a module to manage the ads.txt configuration. This is particularly useful if you have to maintain multiple ads.txt file across all your websites.

## Installing ADS.TXT manager via Composer
You can install ADS.TXT manager using [Composer](https://getcomposer.org) by running the following:

``` bash
$ composer require SPHDD/authorized_digital_sellers:"^1"
```

## Features

1. Able to act as Master-Slave. If you're managing multiple websites with the same ads.txt file everywhere, this will improve your productivity.
2. Serve a cached but outdated ads.txt if the Master fails to respond.
3. Fallback to Self-Managed ads.txt if there is no cached version and Master fails to respond.
4. Ability to set HTTP Cache-Control. Useful if you have HTTP accelerators and CDNs that respect the cache-control setting.

## Configure

- Enable "Authorized Digital Sellers Manager" in Drupal 8
- Delete the physical ads.txt on your root, if any
- Go to configure the Authorized Digital Sellers settings (/admin/config/services/authorized_digital_sellers)
  - External File Management or Self-Managed:
    - Self-Managed: If you want to manage the content independently, or act as the master ads.txt copy
    - External File Management: If you are relying on external ads.txt copy
  - External ADS file
    - Input the absolute URL of the external ads.txt copy, if you selected "External File Management"
  - External ADS file refresh rate
    - The period of time to get a fresh copy of ads.txt from External ADS File
    - The values must be parsable with [strtotime()](http://php.net/manual/en/function.strtotime.php)
    - It must be a figure set in the future (duh!)
    - Leave blank if you want to hit the external ads.txt every time
  - Fallback to Self-Managed
    - Life ain't a bed of roses. If the external ads.txt fails to return, would you like to fallback to the self-managed ADS text file?
  - Self Managed ADS text file
    - If you selected "Self-Managed", then you must input the ads information here
  - HTTP Cache Control
    - Set the period of cache via HTTP
    - The values must be parsable with [strtotime()](http://php.net/manual/en/function.strtotime.php)
    - It must be a figure set in the future (duh!)
    - Leave blank for no-store

## Proposed Future Features

- Detection of ads.txt file and deletion from admin console
- Automatic purging of Varnish ads.txt on fresh copy from Master
- Automatic purging of CDN ads.txt on fresh copy from Master
  - Edgecast
  - Cloudfront