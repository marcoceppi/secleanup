<?php

/// Error codes for the API route /error.
class ErrorCode
{
    /// The server has not found anything matching the Request-URI.
    const NotFound = 404;
    /// A generic error has occurred on the server; developers have been notified.
    const InternalServerError = 500;
    /// The 'key' query parameter containing your application's public key is missing or invalid.
    const InvalidApplicationPublicKey = 4000;
    /// The 'pagesize' query parameter is invalid.
    const InvalidPageSize = 4001;
    /// The 'sort' query parameter was invalid for this request.
    const InvalidSort = 4002;
    /// The 'order' query parameter was invalid for this request.
    const InvalidOrder = 4003;
    /// This IP has exceeded the request-per-day limit.
    const RequestLimitExceeded = 4004;
    /// The vectorized ids were invalid for this request.
    const InvalidVectorFormat = 4005;
    /// There are too many ids in this vector request.
    const TooManyIds = 4006;
    /// 'tagged' or 'intitle' must be set on this method.
    const UnconstrainedSearch = 4007;
    /// The 'tagged' query parameter is missing or invalid for this request.
    const InvalidTags = 4008;
    /// The 'auth' query parameter is not a valid auth token.
    const InvalidAuthToken = 4009;
    /// The 'tags' query parameter is too large.
    const TooManyTags = 4010;
    /// The 'period' query parameter is not one of 'all-time' or 'month'.
    const InvalidPeriod = 4011;
    /// The API is temporarily offline.  Try again later.
    const Offline = 9999;
}

?>