<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectFirebaseInit
{
    /**
     * Inject Firebase initialization after the Firebase SDK and cookie helper are loaded.
     * This keeps legacy Blade pages from calling firebase.firestore() before initializeApp().
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!$response instanceof Response) {
            return $response;
        }

        $content = $response->getContent();

        if (!is_string($content) || stripos($content, 'firebase-app.js') === false) {
            return $response;
        }

        if (stripos($content, 'firebase-init.js') !== false) {
            return $response;
        }

        $script = '<script src="' . asset('js/firebase-init.js') . '?v=62c"></script>';

        $patterns = [
            '#(<script[^>]+src=["\'][^"\']*js/jquery\.cookie\.js[^"\']*["\'][^>]*>\s*</script>)#i',
            '#(<script[^>]+src=["\'][^"\']*firebase-database\.js[^"\']*["\'][^>]*>\s*</script>)#i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, '$1' . PHP_EOL . $script, $content, 1);
                $response->setContent($content);
                return $response;
            }
        }

        return $response;
    }
}
