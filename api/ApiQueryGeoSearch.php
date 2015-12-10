<?php

abstract class ApiQueryGeoSearch extends ApiQueryGeneratorBase {
	const MIN_RADIUS = 10;

	/**
	 * @var Coord The center of search area
	 */
	protected $coord;

	/**
	 * @var BoundingBox Bounding box to search in
	 */
	protected $bbox;

	/**
	 * @var int Search radius
	 */
	protected $radius;

	/**
	 * @var int Id of the page to search around, exclude from results
	 */
	protected $idToExclude;

	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'gs' );
	}

	public function execute() {
		$this->run();
	}

	public function getCacheMode( $params ) {
		return 'public';
	}

	public function executeGenerator( $resultPageSet ) {
		$this->run( $resultPageSet );
	}

	private function parseBbox( $bbox ) {
		global $wgMaxGeoSearchRadius;

		$parts = explode( '|', $bbox );
		$vals = array_map( 'floatval', $parts );
		if ( count( $parts ) != 4
			// Pass $parts here for extra validation
			|| !GeoData::validateCoord( $parts[0], $parts[1] )
			|| !GeoData::validateCoord( $parts[2], $parts[3] )
			|| $vals[0] <= $vals[2]
		) {
			$this->dieUsage( 'Invalid bounding box', '_invalid-bbox' );
		}
		$bbox = new BoundingBox( $vals[0], $vals[1], $vals[2], $vals[3] );
		$area = $bbox->area();
		if ( $area > $wgMaxGeoSearchRadius * $wgMaxGeoSearchRadius * 4
			|| $area < 100
		) {
			$this->dieUsage( 'Bounding box is too big', '_toobig' );
		}

		return $bbox;
	}

	/**
	 * @param ApiPageSet $resultPageSet
	 */
	protected function run( $resultPageSet = null ) {
		$params = $this->extractRequestParams();

		$this->requireOnlyOneParameter( $params, 'coord', 'page', 'bbox' );
		if ( isset( $params['coord'] ) ) {
			$arr = explode( '|', $params['coord'] );
			if ( count( $arr ) != 2 || !GeoData::validateCoord( $arr[0], $arr[1], $params['globe'] ) ) {
				$this->dieUsage( 'Invalid coordinate provided', '_invalid-coord' );
			}
			$this->coord = new Coord( floatval( $arr[0] ), floatval( $arr[1] ), $params['globe'] );
		} elseif ( isset( $params['page'] ) ) {
			$t = Title::newFromText( $params['page'] );
			if ( !$t || !$t->canExist() ) {
				$this->dieUsage( "Invalid page title ``{$params['page']}'' provided", '_invalid-page' );
			}
			if ( !$t->exists() ) {
				$this->dieUsage( "Page ``{$params['page']}'' does not exist", '_nonexistent-page' );
			}
			$this->coord = GeoData::getPageCoordinates( $t );
			if ( !$this->coord ) {
				$this->dieUsage( 'Page coordinates unknown', '_no-coordinates' );
			}
			$this->idToExclude = $t->getArticleID();
		} elseif ( isset( $params['bbox'] ) ) {
			$this->bbox = $this->parseBbox( $params['bbox'] );
			// Even when using bbox, we need a center to sort by distance
			$this->coord = $this->bbox->center();
		} else {
			$this->dieDebug( __METHOD__, 'Logic error' );
		}

		$this->addTables( 'page' );
		// retrieve some fields only if page set needs them
		if ( is_null( $resultPageSet ) ) {
			$this->addFields( array( 'page_id', 'page_namespace', 'page_title' ) );
		} else {
			$this->addFields( WikiPage::selectFields() );
		}
		$this->addWhereFld( 'page_namespace', $params['namespace'] );

		$this->radius = intval( $params['radius'] );

		if ( is_null( $resultPageSet ) ) {
			$this->getResult()->addIndexedTagName( array( 'query', $this->getModuleName() ),
				$this->getModulePrefix()
			);
		}
	}

	/**
	 * Returns a range of tenths of degree
	 *
	 * @param float $start
	 * @param float $end
	 * @param int|null $granularity
	 *
	 * @return array
	 */
	public static function intRange( $start, $end, $granularity = null ) {
		global $wgGeoDataIndexGranularity;

		if ( !$granularity ) {
			$granularity = $wgGeoDataIndexGranularity;
		}
		$start = round( $start * $granularity );
		$end = round( $end * $granularity );
		// @todo: works only on Earth
		if ( $start > $end ) {
			return array_merge(
				range( -180 * $granularity, $end ),
				range( $start, 180 * $granularity )
			);
		} else {
			return range( $start, $end );
		}
	}

	public function getAllowedParams() {
		global $wgMaxGeoSearchRadius, $wgDefaultGlobe, $wgGeoDataDebug;
		$params = array (
			'coord' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_HELP_MSG_APPEND => array(
					'geodata-api-help-coordinates-format',
				),
			),
			'page' => array(
				ApiBase::PARAM_TYPE => 'string',
			),
			'bbox' => array(
				ApiBase::PARAM_TYPE => 'string',
			),
			'radius' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_MIN => self::MIN_RADIUS,
				ApiBase::PARAM_MAX => $wgMaxGeoSearchRadius,
				ApiBase::PARAM_RANGE_ENFORCE => true,
			),
			'maxdim' => array(
				ApiBase::PARAM_TYPE => 'integer',
			),
			'limit' => array(
				ApiBase::PARAM_DFLT => 10,
				ApiBase::PARAM_TYPE => 'limit',
				ApiBase::PARAM_MIN => 1,
				ApiBase::PARAM_MAX => ApiBase::LIMIT_BIG1,
				ApiBase::PARAM_MAX2 => ApiBase::LIMIT_BIG2
			),
			// @todo: globe selection disabled until we have a real use case
			'globe' => array(
				ApiBase::PARAM_TYPE => (array)$wgDefaultGlobe,
				ApiBase::PARAM_DFLT => $wgDefaultGlobe,
			),
			'namespace' => array(
				ApiBase::PARAM_TYPE => 'namespace',
				ApiBase::PARAM_DFLT => NS_MAIN,
				ApiBase::PARAM_ISMULTI => true,
			),
			'prop' => array(
				ApiBase::PARAM_TYPE => array( 'type', 'name', 'dim', 'country', 'region', 'globe' ),
				ApiBase::PARAM_DFLT => 'globe',
				ApiBase::PARAM_ISMULTI => true,
			),
			'primary' => array(
				ApiBase::PARAM_TYPE => array( 'primary', 'secondary', 'all' ),
				ApiBase::PARAM_DFLT => 'primary',
			),
		);
		if ( $wgGeoDataDebug ) {
			$params['debug'] = array(
				ApiBase::PARAM_TYPE => 'boolean',
			);
		}
		return $params;
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=query&list=geosearch&gsradius=10000&gscoord=37.786971|-122.399677'
				=> 'apihelp-query+geosearch-example-1',
			'action=query&list=geosearch&gsbbox=37.8|-122.3|37.7|-122.4'
				=> 'apihelp-query+geosearch-example-2',
		);
	}

	public function getHelpUrls() {
		return 'https://www.mediawiki.org/wiki/Extension:GeoData#list.3Dgeosearch';
	}
}
