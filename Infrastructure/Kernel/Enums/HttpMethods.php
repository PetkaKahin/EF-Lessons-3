<?php

namespace Infrastructure\Kernel\Enums;

enum HttpMethods: string
{
    case GET = 'GET';
    case POST = 'POST';
    case UPDATE = 'UPDATE';
    case PUT = 'PUT';
    case PATCH = 'PATCH';
    case DELETE = 'DELETE';
}
