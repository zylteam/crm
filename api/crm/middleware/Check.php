<?php

namespace app\http\middleware;

class Check
{
    public function handle($request, \Closure $next, $name)
    {
        if ($name == 'think') {
            return redirect('index/think');
        }

        return $next($request);
    }
}
