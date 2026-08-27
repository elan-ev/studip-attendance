<?php
/**
 * Threshold Index Route Handler
 *
 * @package   StudipAttendance\JsonApi\Routes
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipAttendance\JsonApi\Routes\Threshold;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipAttendance\JsonApi\Routes\Authority;
use StudipAttendance\JsonApi\Schemas\AttendanceThresholdSchema;
use StudipAttendance\Models\AttendanceThreshold;

use Seminar;

class Index extends JsonApiController
{
    /**
     * @inheritDoc
     */
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @inheritDoc
     */
    protected $allowedIncludePaths = [
        AttendanceThresholdSchema::REL_COURSE,
    ];

    /**
     * Index Thresholds.
     * @param Request $request
     * @param Response $response
     * @param mixed $args
     * @return void
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $user = $this->getUser($request);

        if (!Authority::canIndexThreshold($user)) {
            throw new AuthorizationFailedException();
        }

        [$offset, $limit] = $this->getOffsetAndLimit();

        $thresholds = AttendanceThreshold::getAll();
        $total = count($thresholds);
        $data = array_slice($thresholds, $offset, $limit);

        return $this->getPaginatedContentResponse($data, $total);
    }
}
