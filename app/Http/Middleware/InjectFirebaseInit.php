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

        $scripts = '';

        if (stripos($content, 'firebase-init.js') === false) {
            $scripts .= '<script src="' . asset('js/firebase-init.js') . '?v=62c2"></script>' . PHP_EOL;
        }

        if (stripos($content, 'storage-image-normalizer.js') === false) {
            $scripts .= '<script src="' . asset('js/storage-image-normalizer.js') . '?v=62d"></script>' . PHP_EOL;
        }

        if ($scripts === '') {
            return $response;
        }

        $patterns = [
            '#(<script[^>]+src=["\'][^"\']*js/jquery\.cookie\.js[^"\']*["\'][^>]*>\s*</script>)#i',
            '#(<script[^>]+src=["\'][^"\']*firebase-database\.js[^"\']*["\'][^>]*>\s*</script>)#i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, '$1' . PHP_EOL . $scripts, $content, 1);
                $response->setContent($content);
                return $response;
            }
        }

        return $response;
    }
}
