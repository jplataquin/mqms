<?php

use Laravel\Mcp\Server\Facades\Mcp;

Mcp::web('/', \App\Mcp\Servers\MqmsServer::class);
