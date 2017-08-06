<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/7/22
 * Time: 15:39
 * Desc: -
 */


function sendHttpStatus($code)
{
    $statuses = [
        // INFORMATIONAL CODES
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        // SUCCESS CODES
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        226 => 'IM Used',
        // REDIRECTION CODES
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy', // Deprecated
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        // CLIENT ERROR
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        499 => 'Client Closed Request',
        // SERVER ERROR
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];
    if (isset($statuses[$code])) {
        header('HTTP/1.1 ' . $code . ' ' . $statuses[$code]);
        header('Status:' . $code . ' ' . $statuses[$code]);
    }
}

/**
 * @param Throwable $exception
 */
function exceptionHandler($exception)
{
    profilerStop();
    Log::exception($exception);
    if ($exception instanceof HttpException) {
        $response = $exception->toResponse();
        $response->send();
        return;
    }
    sendHttpStatus(500);
    header('content-type: application/json');
    echo json_encode([
        'error_code' => 500,
        'error_msg' => 'Server down, please check log!',
    ]);
}

function errorHandler($errNo, $errStr, $errFile, $errLine)
{
    $levels = [
        E_WARNING => 'Warning',
        E_NOTICE => 'Notice',
        E_STRICT => 'Strict',
        E_DEPRECATED => 'Deprecated',
    ];
    $errLevel = $errNo;
    isset($levels[$errNo]) and $errLevel = $levels[$errNo];
    throw new ErrorException($errLevel . ' - ' . $errStr, $errNo, 1, $errFile, $errLine);
}

function profilerStart()
{
    if (isset($_SERVER['ENABLE_PROFILER']) && function_exists('xhprof_enable')) {
        xhprof_enable(0, [
            'ignored_functions' => [
                'call_user_func',
                'call_user_func_array',
            ],
        ]);
    }
}

function profilerStop($type = 'fpm')
{
    if (isset($_SERVER['ENABLE_PROFILER']) && function_exists('xhprof_enable')) {
        static $profiler;
        static $profilerDir;
        $data = xhprof_disable();
        $profilerDir || is_dir($profilerDir = storagePath('profiler')) or mkdir($profilerDir, 0777, true);
        $pathInfo = strtr($_SERVER['REQUEST_URI'], ['/' => '|']);
        $microTime = explode(' ', microtime());
        $reportFile = $microTime[1] . '-' . substr($microTime[0], 2) . '-' . $_SERVER['REQUEST_METHOD'] . $pathInfo;
        $profiler or $profiler = new XHProfRuns_Default($profilerDir);
        $profiler->save_run($data, $type, $reportFile);
    }
}


function getMimes() {
    return [
        'application/atom+xml' => 'atom',
        'application/directx' => 'x',
        'application/envoy' => 'evy',
        'application/fractals' => 'fif',
        'application/futuresplash' => 'spl',
        'application/hta' => 'hta',
        'application/internet-property-stream' => 'acx',
        'application/java-archive' => 'jar',
        'application/liquidmotion' => 'jcz',
        'application/mac-binhex40' => 'hqx',
        'application/msaccess' => 'accdb',
        'application/msword' => 'dot',
        'application/octet-stream' => 'bin',
        'application/oda' => 'oda',
        'application/oleobject' => 'ods',
        'application/olescript' => 'axs',
        'application/onenote' => 'one',
        'application/pdf' => 'pdf',
        'application/pics-rules' => 'prf',
        'application/pkcs10' => 'p10',
        'application/pkcs7-mime' => 'p7c',
        'application/pkcs7-signature' => 'p7s',
        'application/pkix-crl' => 'crl',
        'application/postscript' => 'eps',
        'application/rtf' => 'rtf',
        'application/set-payment-initiation' => 'setpay',
        'application/set-registration-initiation' => 'setreg',
        'application/streamingmedia' => 'ssm',
        'application/vnd.fdf' => 'fdf',
        'application/vnd.ms-excel' => 'xlm',
        'application/vnd.ms-excel.addin.macroEnabled.12' => 'xlam',
        'application/vnd.ms-excel.sheet.binary.macroEnabled.12' => 'xlsb',
        'application/vnd.ms-excel.sheet.macroEnabled.12' => 'xlsm',
        'application/vnd.ms-excel.template.macroEnabled.12' => 'xltm',
        'application/vnd.ms-officetheme' => 'thmx',
        'application/vnd.ms-pki.certstore' => 'sst',
        'application/vnd.ms-pki.pko' => 'pko',
        'application/vnd.ms-pki.seccat' => 'cat',
        'application/vnd.ms-pki.stl' => 'stl',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/vnd.ms-powerpoint.addin.macroEnabled.12' => 'ppam',
        'application/vnd.ms-powerpoint.presentation.macroEnabled.12' => 'pptm',
        'application/vnd.ms-powerpoint.slide.macroEnabled.12' => 'sldm',
        'application/vnd.ms-powerpoint.slideshow.macroEnabled.12' => 'ppsm',
        'application/vnd.ms-powerpoint.template.macroEnabled.12' => 'potm',
        'application/vnd.ms-project' => 'mpp',
        'application/vnd.ms-visio.viewer' => 'vdx',
        'application/vnd.ms-word.document.macroEnabled.12' => 'docm',
        'application/vnd.ms-word.template.macroEnabled.12' => 'dotm',
        'application/vnd.ms-works' => 'wps',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'application/vnd.openxmlformats-officedocument.presentationml.slide' => 'sldx',
        'application/vnd.openxmlformats-officedocument.presentationml.slideshow' => 'ppsx',
        'application/vnd.openxmlformats-officedocument.presentationml.template' => 'potx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.template' => 'xltx',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.template' => 'dotx',
        'application/vnd.rn-realmedia' => 'rm',
        'application/vnd.visio' => 'vst',
        'application/vnd.wap.wmlc' => 'wmlc',
        'application/vnd.wap.wmlscriptc' => 'wmlsc',
        'application/winhlp' => 'hlp',
        'application/x-bcpio' => 'bcpio',
        'application/x-cdf' => 'cdf',
        'application/x-compress' => 'z',
        'application/x-compressed' => 'tgz',
        'application/x-cpio' => 'cpio',
        'application/x-csh' => 'csh',
        'application/x-director' => 'dir',
        'application/x-dvi' => 'dvi',
        'application/x-gtar' => 'gtar',
        'application/x-gzip' => 'gz',
        'application/x-hdf' => 'hdf',
        'application/x-internet-signup' => 'isp',
        'application/x-iphone' => 'iii',
        'application/x-java-applet' => 'class',
        'application/x-latex' => 'latex',
        'application/x-miva-compiled' => 'mvc',
        'application/x-ms-reader' => 'lit',
        'application/x-ms-wmd' => 'wmd',
        'application/x-ms-wmz' => 'wmz',
        'application/x-msaccess' => 'mdb',
        'application/x-mscardfile' => 'crd',
        'application/x-msclip' => 'clp',
        'application/x-msdownload' => 'dll',
        'application/x-msmediaview' => 'mvb',
        'application/x-msmetafile' => 'wmf',
        'application/x-msmoney' => 'mny',
        'application/x-mspublisher' => 'pub',
        'application/x-msschedule' => 'scd',
        'application/x-msterminal' => 'trm',
        'application/x-mswrite' => 'wri',
        'application/x-netcdf' => 'nc',
        'application/x-oleobject' => 'hhc',
        'application/x-perfmon' => 'pmc',
        'application/x-pkcs12' => 'pfx',
        'application/x-pkcs7-certificates' => 'spc',
        'application/x-pkcs7-certreqresp' => 'p7r',
        'application/x-quicktimeplayer' => 'qtl',
        'application/x-sh' => 'sh',
        'application/x-shar' => 'shar',
        'application/x-shockwave-flash' => 'swf',
        'application/x-smaf' => 'mmf',
        'application/x-stuffit' => 'sit',
        'application/x-sv4cpio' => 'sv4cpio',
        'application/x-sv4crc' => 'sv4crc',
        'application/x-tar' => 'tar',
        'application/x-tcl' => 'tcl',
        'application/x-tex' => 'tex',
        'application/x-texinfo' => 'texi',
        'application/x-troff' => 't',
        'application/x-troff-man' => 'man',
        'application/x-troff-me' => 'me',
        'application/x-troff-ms' => 'ms',
        'application/x-ustar' => 'ustar',
        'application/x-wais-source' => 'src',
        'application/x-x509-ca-cert' => 'der',
        'application/x-zip-compressed' => 'zip',
        'application/zip' => 'zip',
        'audio/aiff' => 'aifc',
        'audio/basic' => 'snd',
        'audio/mid' => 'midi',
        'audio/mpeg' => 'mp3',
        'audio/wav' => 'wav',
        'audio/x-aiff' => 'aif',
        'audio/x-mpegurl' => 'm3u',
        'audio/x-ms-wax' => 'wax',
        'audio/x-ms-wma' => 'wma',
        'audio/x-pn-realaudio' => 'ra',
        'audio/x-pn-realaudio-plugin' => 'rpm',
        'audio/x-smd' => 'smd',
        'drawing/x-dwf' => 'dwf',
        'image/bmp' => 'dib',
        'image/cis-cod' => 'cod',
        'image/gif' => 'gif',
        'image/ief' => 'ief',
        'image/jpeg' => 'jpg',
        'image/pjpeg' => 'jfif',
        'image/png' => 'png',
        'image/tiff' => 'tiff',
        'image/vnd.rn-realflash' => 'rf',
        'image/vnd.wap.wbmp' => 'wbmp',
        'image/x-cmu-raster' => 'ras',
        'image/x-cmx' => 'cmx',
        'image/x-icon' => 'ico',
        'image/x-jg' => 'art',
        'image/x-portable-anymap' => 'pnm',
        'image/x-portable-bitmap' => 'pbm',
        'image/x-portable-graymap' => 'pgm',
        'image/x-portable-pixmap' => 'ppm',
        'image/x-rgb' => 'rgb',
        'image/x-xbitmap' => 'xbm',
        'image/x-xpixmap' => 'xpm',
        'image/x-xwindowdump' => 'xwd',
        'message/rfc822' => 'mhtml',
        'text/css' => 'css',
        'text/dlm' => 'dlm',
        'text/h323' => '323',
        'text/html' => 'html',
        'text/iuls' => 'uls',
        'text/plain' => 'h',
        'text/richtext' => 'rtx',
        'text/scriptlet' => 'sct',
        'text/sgml' => 'sgml',
        'text/tab-separated-values' => 'tsv',
        'text/vbscript' => 'vbs',
        'text/vnd.wap.wml' => 'wml',
        'text/vnd.wap.wmlscript' => 'wmls',
        'text/webviewhtml' => 'htt',
        'text/x-component' => 'htc',
        'text/x-hdml' => 'hdml',
        'text/x-setext' => 'etx',
        'text/x-vcard' => 'vcf',
        'text/xml' => 'xml',
        'video/mpeg' => 'mpv2',
        'video/quicktime' => 'qt',
        'video/x-ivf' => 'IVF',
        'video/x-la-asf' => 'lsf',
        'video/x-ms-wm' => 'wm',
        'video/x-ms-wmp' => 'wmp',
        'video/x-ms-wmv' => 'wmv',
        'video/x-ms-wmx' => 'wmx',
        'video/x-ms-wvx' => 'wvx',
        'video/x-msvideo' => 'avi',
        'video/x-sgi-movie' => 'movie',
        'x-world/x-vrml' => 'xaf',
        'video/x-ms-asf' => 'asx',
    ];
}


function getMillisecond() {
    list($t1, $t2) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
}


/**
 * 获取当前服务器ip
 * @return string
 */
function getLocalIp() {
    static $currentIP;

    if ($currentIP == null) {
        $serverIps = \swoole_get_local_ip();
        $patternArray = array(
            '10\.',
            '172\.1[6-9]\.',
            '172\.2[0-9]\.',
            '172\.31\.',
            '192\.168\.'
        );
        foreach ($serverIps as $serverIp) {
            // 匹配内网IP
            if (preg_match('#^' . implode('|', $patternArray) . '#', $serverIp)) {
                $currentIP = $serverIp;
                return $currentIP;
            }
        }
    }

    return $currentIP;
}



/**
 * 检查端口是否可以被绑定
 */
function isPortBinded($hostname = '127.0.0.1', $port = 80, $timeout = 5) {
    $res = false; //端口未绑定
    $fp	= @fsockopen($hostname, $port, $errno, $errstr, $timeout);
    if($errno == 0 && $fp!=false){
        fclose($fp);
        $res = true; //端口已绑定
    }

    return $res;
}