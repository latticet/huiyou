<?php

return array(
	'get' => array(
		'/\/api\/zc888\/\d+/',
		'/\/api\/users/',
		'/\/api\/users\//',
		'/\/api\/users\/pages/',
		'/\/api\/users\/\d+/',
		'/\/api\/mobileschools\/\w+/',
		'/\/api\/courses\/blocks/',
		'/\/api\/articles.*/',
		'/\/api\/courses\/category\/.*/',
        '/\/api\/activity\/\w+/',
        '/\/api\/courses\/home\/.*/'
	),
	'post' => array(
		'/\/api\/users/',
		'/\/api\/users\//',
		'/\/api\/users\/login/',
		'/\/api\/users\/bind_login/'
	)
);