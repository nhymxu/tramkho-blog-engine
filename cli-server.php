<?php

error_reporting(E_ALL ^ E_STRICT);
ini_set('display_errors', 'stderr');

class CliServer
{
    public static function log($status = 200): void
    {
        file_put_contents(
            "php://stdout",
            sprintf(
                "[%s] %s:%s [%s]: %s\n",
                date("D M j H:i:s Y"),
                $_SERVER["REMOTE_ADDR"],
                $_SERVER["REMOTE_PORT"],
                $status,
                $_SERVER["REQUEST_URI"]
            )
        );
    }

    public static function str_starts_with(string $haystack, string $needle): bool
    {
        return 0 === \strncmp($haystack, $needle, \strlen($needle));
    }

    public static function get_content_type($file) {
        $types = array(
            'ai'      => 'application/postscript',
            'aif'     => 'audio/x-aiff',
            'aifc'    => 'audio/x-aiff',
            'aiff'    => 'audio/x-aiff',
            'asc'     => 'text/plain',
            'atom'    => 'application/atom+xml',
            'au'      => 'audio/basic',
            'avi'     => 'video/x-msvideo',
            'bcpio'   => 'application/x-bcpio',
            'bin'     => 'application/octet-stream',
            'bmp'     => 'image/bmp',
            'cdf'     => 'application/x-netcdf',
            'cgm'     => 'image/cgm',
            'class'   => 'application/octet-stream',
            'cpio'    => 'application/x-cpio',
            'cpt'     => 'application/mac-compactpro',
            'csh'     => 'application/x-csh',
            'css'     => 'text/css',
            'csv'     => 'text/csv',
            'dcr'     => 'application/x-director',
            'dir'     => 'application/x-director',
            'djv'     => 'image/vnd.djvu',
            'djvu'    => 'image/vnd.djvu',
            'dll'     => 'application/octet-stream',
            'dmg'     => 'application/octet-stream',
            'dms'     => 'application/octet-stream',
            'doc'     => 'application/msword',
            'dtd'     => 'application/xml-dtd',
            'dvi'     => 'application/x-dvi',
            'dxr'     => 'application/x-director',
            'eps'     => 'application/postscript',
            'etx'     => 'text/x-setext',
            'exe'     => 'application/octet-stream',
            'ez'      => 'application/andrew-inset',
            'gif'     => 'image/gif',
            'gram'    => 'application/srgs',
            'grxml'   => 'application/srgs+xml',
            'gtar'    => 'application/x-gtar',
            'hdf'     => 'application/x-hdf',
            'hqx'     => 'application/mac-binhex40',
            'htm'     => 'text/html',
            'html'    => 'text/html',
            'ice'     => 'x-conference/x-cooltalk',
            'ico'     => 'image/x-icon',
            'ics'     => 'text/calendar',
            'ief'     => 'image/ief',
            'ifb'     => 'text/calendar',
            'iges'    => 'model/iges',
            'igs'     => 'model/iges',
            'jpe'     => 'image/jpeg',
            'jpeg'    => 'image/jpeg',
            'jpg'     => 'image/jpeg',
            'js'      => 'application/x-javascript',
            'json'    => 'application/json',
            'kar'     => 'audio/midi',
            'latex'   => 'application/x-latex',
            'lha'     => 'application/octet-stream',
            'lzh'     => 'application/octet-stream',
            'm3u'     => 'audio/x-mpegurl',
            'man'     => 'application/x-troff-man',
            'mathml'  => 'application/mathml+xml',
            'me'      => 'application/x-troff-me',
            'mesh'    => 'model/mesh',
            'mid'     => 'audio/midi',
            'midi'    => 'audio/midi',
            'mif'     => 'application/vnd.mif',
            'mov'     => 'video/quicktime',
            'movie'   => 'video/x-sgi-movie',
            'mp2'     => 'audio/mpeg',
            'mp3'     => 'audio/mpeg',
            'mpe'     => 'video/mpeg',
            'mpeg'    => 'video/mpeg',
            'mpg'     => 'video/mpeg',
            'mpga'    => 'audio/mpeg',
            'ms'      => 'application/x-troff-ms',
            'msh'     => 'model/mesh',
            'mxu'     => 'video/vnd.mpegurl',
            'nc'      => 'application/x-netcdf',
            'oda'     => 'application/oda',
            'ogg'     => 'application/ogg',
            'pbm'     => 'image/x-portable-bitmap',
            'pdb'     => 'chemical/x-pdb',
            'pdf'     => 'application/pdf',
            'pgm'     => 'image/x-portable-graymap',
            'pgn'     => 'application/x-chess-pgn',
            'png'     => 'image/png',
            'pnm'     => 'image/x-portable-anymap',
            'ppm'     => 'image/x-portable-pixmap',
            'ppt'     => 'application/vnd.ms-powerpoint',
            'ps'      => 'application/postscript',
            'qt'      => 'video/quicktime',
            'ra'      => 'audio/x-pn-realaudio',
            'ram'     => 'audio/x-pn-realaudio',
            'ras'     => 'image/x-cmu-raster',
            'rdf'     => 'application/rdf+xml',
            'rgb'     => 'image/x-rgb',
            'rm'      => 'application/vnd.rn-realmedia',
            'roff'    => 'application/x-troff',
            'rss'     => 'application/rss+xml',
            'rtf'     => 'text/rtf',
            'rtx'     => 'text/richtext',
            'sgm'     => 'text/sgml',
            'sgml'    => 'text/sgml',
            'sh'      => 'application/x-sh',
            'shar'    => 'application/x-shar',
            'silo'    => 'model/mesh',
            'sit'     => 'application/x-stuffit',
            'skd'     => 'application/x-koan',
            'skm'     => 'application/x-koan',
            'skp'     => 'application/x-koan',
            'skt'     => 'application/x-koan',
            'smi'     => 'application/smil',
            'smil'    => 'application/smil',
            'snd'     => 'audio/basic',
            'so'      => 'application/octet-stream',
            'spl'     => 'application/x-futuresplash',
            'src'     => 'application/x-wais-source',
            'sv4cpio' => 'application/x-sv4cpio',
            'sv4crc'  => 'application/x-sv4crc',
            'svg'     => 'image/svg+xml',
            'svgz'    => 'image/svg+xml',
            'swf'     => 'application/x-shockwave-flash',
            't'       => 'application/x-troff',
            'tar'     => 'application/x-tar',
            'tcl'     => 'application/x-tcl',
            'tex'     => 'application/x-tex',
            'texi'    => 'application/x-texinfo',
            'texinfo' => 'application/x-texinfo',
            'tif'     => 'image/tiff',
            'tiff'    => 'image/tiff',
            'tr'      => 'application/x-troff',
            'tsv'     => 'text/tab-separated-values',
            'txt'     => 'text/plain',
            'ustar'   => 'application/x-ustar',
            'vcd'     => 'application/x-cdlink',
            'vrml'    => 'model/vrml',
            'vxml'    => 'application/voicexml+xml',
            'wav'     => 'audio/x-wav',
            'wbmp'    => 'image/vnd.wap.wbmp',
            'wbxml'   => 'application/vnd.wap.wbxml',
            'wml'     => 'text/vnd.wap.wml',
            'wmlc'    => 'application/vnd.wap.wmlc',
            'wmls'    => 'text/vnd.wap.wmlscript',
            'wmlsc'   => 'application/vnd.wap.wmlscriptc',
            'woff'    => 'application/x-font-woff',
            'wrl'     => 'model/vrml',
            'xbm'     => 'image/x-xbitmap',
            'xht'     => 'application/xhtml+xml',
            'xhtml'   => 'application/xhtml+xml',
            'xls'     => 'application/vnd.ms-excel',
            'xml'     => 'application/xml',
            'xpm'     => 'image/x-xpixmap',
            'xsl'     => 'application/xml',
            'xslt'    => 'application/xslt+xml',
            'xul'     => 'application/vnd.mozilla.xul+xml',
            'xwd'     => 'image/x-xwindowdump',
            'xyz'     => 'chemical/x-xyz',
            'zip'     => 'application/zip'
        );

        $ext = pathinfo($file, PATHINFO_EXTENSION);

        if (isset($types[$ext])) {
            return $types[$ext];
        }

        return false;
    }

    public static function is_static_file($url_path): bool
    {
        return is_file(__DIR__ . $url_path) && file_exists(__DIR__ . $url_path);
    }

    public static function fix_url_route($path): string
    {
        $script = 'index.php';
        if (strpos($path, '.php') !== FALSE) {
            // Work backwards through the path to check if a script exists. Otherwise
            // fallback to index.php.
            do {
                $path = dirname($path);
                if (preg_match('/\.php$/', $path) && is_file(__DIR__ . $path)) {
                    // Discovered that the path contains an existing PHP file. Use that as the
                    // script to include.
                    $script = ltrim($path, '/');
                    break;
                }
            } while ($path !== '/' && $path !== '.');
        }

        return $script;
    }

    public static function serve_static_theme($url_path): bool
    {
        $file_path = __DIR__ . '/storage/templates/' . str_replace('/static/theme/', '', $url_path);

        $content_type = self::get_content_type($file_path);

        if ($content_type) {
            header('Content-Type: ' . $content_type);
            readfile($file_path);
            return true;
        }

        return false;
    }

    public static function serve($url_path): void
    {
        // Work around the PHP bug.
        $script = self::fix_url_route($url_path);

        // Update $_SERVER variables to point to the correct index-file.
        $index_file_absolute = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $script;
        $index_file_relative = DIRECTORY_SEPARATOR . $script;

        // SCRIPT_FILENAME will point to the router script itself, it should point to
        // the full path of index.php.
        $_SERVER['SCRIPT_FILENAME'] = $index_file_absolute;

        // SCRIPT_NAME and PHP_SELF will either point to index.php or contain the full
        // virtual path being requested depending on the URL being requested. They
        // should always point to index.php relative to document root.
        $_SERVER['SCRIPT_NAME'] = $index_file_relative;
        $_SERVER['PHP_SELF'] = $index_file_relative;

        // Require the script and let core take over.
        require $_SERVER['SCRIPT_FILENAME'];

        self::log(http_response_code());
    }
}


/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
//chdir(dirname(__DIR__));

if (PHP_SAPI !== 'cli-server') {
    die('this is only for the php development server');
}

$url_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Decline static file requests back to the PHP built-in webserver
if (CliServer::is_static_file($url_path)) {
    // Serve the requested resource as-is.
    return false;
}

if (CliServer::str_starts_with($url_path, '/static/theme/')) {
    return CliServer::serve_static_theme($url_path);
}
// https://github.com/skilld-labs/docker-php/blob/master/.ht.router.php

CliServer::serve($url_path);
