<?php

namespace Sys\Profiler;

use Az\Route\RouteCollectionInterface;
use Psr\Http\Message\ResponseInterface;
use Sys\Profiler\Model\ProfilerModelInterface;

final class Profiler
{
    private ProfilerModelInterface $model;

    public function __construct(ProfilerModelInterface $model, RouteCollectionInterface $route)
    {
        $this->model = $model;
        $this->model->setProfiling();
    }

    public function __invoke(ResponseInterface $response, string $mode)
    {
        if (strpos($_SERVER['REQUEST_URI'], '/media/0/vendor/az/sys/src/Profiler/profiler.js') !== 0 
            && strpos($_SERVER['REQUEST_URI'], '/~profiler/') !== 0 && $mode === 'web') {
            $stream = $response->getBody();
            $size = $stream->getSize();
            register_shutdown_function([$this,'shutdown'], $size);
        }

        return $response;
    }

    public function shutdown(int $size)
    {
        $time = (hrtime(true) - $GLOBALS['_start'])/1e+6;
        
        $array = $this->model->showProfiles();
        $profiler = $this->model->get($_SERVER['REQUEST_URI']);
        $data['queries'] = count($array);

        $duration = 0;
        $profiles = [];

        foreach ($array as $row) {
            $row = (object) $row;
            $duration += $row->Duration * 1000;
            $profiles[] = $row->Query;
        }

        $data['uri'] = $_SERVER['REQUEST_URI'];
        $data['size'] = $size;
        $data['time'] = ($profiler) ? $this->avg($time, $profiler->time, $profiler->counter) : $time;
        $data['memory'] = memory_get_usage() - $GLOBALS['_ram'];
        $data['duration'] = ($profiler) ? $this->avg($duration, $profiler->duration, $profiler->counter) : $duration;
        $data['counter'] = ($profiler) ? ++$profiler->counter : 1;
        $data['profiles'] = json_encode($profiles);

        $this->model->set($data);

        echo '<br><script src="/media/0/vendor/az/sys/src/Profiler/profiler.js"></script>';
    }

    private function avg($value, $average, $count)
    {
        return (($average * $count) + $value) / ($count + 1);
    }
}
