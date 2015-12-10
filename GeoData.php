<?php
/**
 * GeoData extension. Initial author Max Semenik
 * License: WTFPL 2.0
 */
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'GeoData',
	'author' => array( 'Max Semenik' ),
	'url' => 'https://www.mediawiki.org/wiki/Extension:GeoData',
	'descriptionmsg' => 'geodata-desc',
);

$dir = __DIR__;

$wgAutoloadClasses['ApiQueryCoordinates'] = "$dir/api/ApiQueryCoordinates.php";
$wgAutoloadClasses['ApiQueryGeoSearch'] = "$dir/api/ApiQueryGeoSearch.php";
$wgAutoloadClasses['ApiQueryGeoSearchDb'] = "$dir/api/ApiQueryGeoSearchDb.php";
$wgAutoloadClasses['ApiQueryGeoSearchElastic'] = "$dir/api/ApiQueryGeoSearchElastic.php";

$wgAutoloadClasses['BoundingBox'] = "$dir/includes/BoundingBox.php";
$wgAutoloadClasses['Coord'] = "$dir/includes/Coord.php";
$wgAutoloadClasses['CoordinatesOutput'] = "$dir/includes/CoordinatesOutput.php";
$wgAutoloadClasses['CoordinatesParserFunction'] = "$dir/includes/CoordinatesParserFunction.php";
$wgAutoloadClasses['GeoData'] = "$dir/includes/GeoData.body.php";
$wgAutoloadClasses['GeoDataHooks'] = "$dir/includes/GeoDataHooks.php";
$wgAutoloadClasses['GeoDataMath'] = "$dir/includes/GeoDataMath.php";

$wgMessagesDirs['GeoData'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['GeoData'] = "$dir/GeoData.i18n.php";
$wgExtensionMessagesFiles['GeoDataMagic'] = "$dir/GeoData.i18n.magic.php";

$wgAPIPropModules['coordinates'] = 'ApiQueryCoordinates';

$wgHooks['LoadExtensionSchemaUpdates'][] = 'GeoDataHooks::onLoadExtensionSchemaUpdates';
$wgHooks['ParserFirstCallInit'][] = 'GeoDataHooks::onParserFirstCallInit';
$wgHooks['UnitTestsList'][] = 'GeoDataHooks::onUnitTestsList';
$wgHooks['ArticleDeleteComplete'][] = 'GeoDataHooks::onArticleDeleteComplete';
$wgHooks['LinksUpdate'][] = 'GeoDataHooks::onLinksUpdate';
$wgHooks['FileUpload'][] = 'GeoDataHooks::onFileUpload';
$wgHooks['OutputPageParserOutput'][] = 'GeoDataHooks::onOutputPageParserOutput';
$wgHooks['CirrusSearchMappingConfig'][] = 'GeoDataHooks::onCirrusSearchMappingConfig';
$wgHooks['CirrusSearchBuildDocumentParse'][] = 'GeoDataHooks::onCirrusSearchBuildDocumentParse';
$wgHooks['ParserTestTables'][] = 'GeoDataHooks::onParserTestTables';

// Use the proper search backend
$wgExtensionFunctions[] = 'efInitGeoData';

function efInitGeoData() {
	global $wgGeoDataBackend, $wgAPIListModules;
	if ( !isset( $wgAPIListModules['geosearch'] ) ) {
		$wgAPIListModules['geosearch'] = 'ApiQueryGeoSearch' . ucfirst( $wgGeoDataBackend );
	}
}

// Tracking categories for Special:TrackingCategories
$wgTrackingCategories[] = 'geodata-broken-tags-category';
$wgTrackingCategories[] = 'geodata-unknown-globe-category';
$wgTrackingCategories[] = 'geodata-unknown-region-category';
$wgTrackingCategories[] = 'geodata-unknown-type-category';

// =================== start configuration settings ===================

/**
 * Maximum radius in metres for geospatial searches around a point.
 * For bounding box based searches, the area must not exceed R^2*4.
 *
 * The greater this variable is, the louder your server ouches.
 */
$wgMaxGeoSearchRadius = 10000; // 10km

/**
 * Default value for the globe (planet/astral body the coordinate is on)
 */
$wgDefaultGlobe = 'earth';

/**
 * Maximum number of coordinates per page, -1 means no limit
 */
$wgMaxCoordinatesPerPage = 500;

/**
 * Conversion table type --> dim
 */
$wgTypeToDim = array(
	'country'        => 1000000,
	'satellite'      => 1000000,
	'state'          => 300000,
	'adm1st'         => 100000,
	'adm2nd'         => 30000,
	'adm3rd'         => 10000,
	'city'           => 10000,
	'isle'           => 10000,
	'mountain'       => 10000,
	'river'          => 10000,
	'waterbody'      => 10000,
	'event'          => 5000,
	'forest'         => 5000,
	'glacier'        => 5000,
	'airport'        => 3000,
	'railwaystation' => 1000,
	'edu'            => 1000,
	'pass'           => 1000,
	'camera'         => 1000,
	'landmark'       => 1000,
);

/**
 * Default value of dim if it is unknown
 */
$wgDefaultDim = 1000;

$earth = array( 'min' => -180, 'mid' => 0, 'max' => 180, 'abbr' => array( 'E' => +1, 'W' => -1 ), 'wrap' => false );
$east360 = array( 'min' => 0, 'mid' => 180, 'max' => 360, 'abbr' => array( 'E' => +1, 'W' => -1 ), 'wrap' => true );
$west360 = array( 'min' => 0, 'mid' => 180, 'max' => 360, 'abbr' => array( 'E' => -1, 'W' => +1 ), 'wrap' => true );

/**
 * Description of coordinate systems, mostly taken from http://planetarynames.wr.usgs.gov/TargetCoordinates
 */
$wgGlobes = array(
	'earth' => $earth,
	'mercury' => $west360,
	'venus' => $east360,
	'moon' => $earth,
	'mars' => $east360,
	'phobos' => $west360,
	'deimos' => $west360,
	// 'ceres' => ???,
	// 'vesta' => ???,
	'ganymede' => $west360,
	'callisto' => $west360,
	'io' => $west360,
	'europa' => $west360,
	'mimas' => $west360,
	'enceladus' => $west360,
	'tethys' => $west360,
	'dione' => $west360,
	'rhea' => $west360,
	'titan' => $west360,
	'hyperion' => $west360,
	'iapetus' => $west360,
	'phoebe' => $west360,
	'miranda' => $east360,
	'ariel' => $east360,
	'umbriel' => $east360,
	'titania' => $east360,
	'oberon' => $east360,
	'triton' => $east360,
	'pluto' => $east360, // ???
);

unset( $earth );
unset( $east360 );
unset( $west360 );

/**
 * Controls what GeoData should do when it encounters some problem.
 * Reaction type:
 *  - track - Add tracking category
 *  - fail - Consider the tag invalid, display message and add tracking category
 *  - none - Do nothing
 */
$wgGeoDataWarningLevel = array(
	'unknown type' => 'track',
	'unknown globe' => 'none',
	'invalid region' => 'track',
);

/**
 * How many gt_(lat|lon)_int units per degree
 * Run updateIndexGranularity.php after changing this
 */
$wgGeoDataIndexGranularity = 10;

/**
 * Which backend should be used by spatial searhces: 'db' or 'elastic'
 */
$wgGeoDataBackend = 'db';

/**
 * Specifies which information about page's primary coordinate is added to global JS variable wgCoordinates.
 * Setting it to false or empty array will disable wgCoordinates.
 */
$wgGeoDataInJS = array( 'lat', 'lon' );

/**
 * Enables the use of GeoData as a CirrusSearch plugin for indexing.
 * This is separate from $wgGeoDataBackend: you could be filling Elasticsearch index and using old search
 * meanwhile. However, if backend is already set to 'elastic', GeoData always behaves as if it's true
 */
$wgGeoDataUseCirrusSearch = false;

/**
 * Controls whether Elasticsearch searches should use indexed bounding box optimization.
 */
$wgGeoDataIndexLatLon = true;

/**
 * Lossy coordinate compression for Elasticsearch backend
 * Possible values: false (no compression), '1mm', '1cm', '3m', '1km'
 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/master/mapping-geo-point-type.html#_field_data
 */
$wgGeoDataCoordinatesCompression = false;

/**
 * If set to true, will add debug information to API output
 */
$wgGeoDataDebug = false;
