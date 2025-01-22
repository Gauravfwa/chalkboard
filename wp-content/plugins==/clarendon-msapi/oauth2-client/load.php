<?php
		require 'guzzle/psr/MessageInterface.php';	
		require 'guzzle/psr/UriInterface.php';	
		require 'guzzle/psr/StreamInterface.php';		
		require 'guzzle/psr/RequestInterface.php';
		require 'guzzle/psr/ResponseInterface.php';		
		require 'guzzle/psr/ClientExceptionInterface.php';	
		require 'guzzle/psr/RequestExceptionInterface.php';			
		
		require 'guzzle/promises/PromiseInterface.php';		
		require 'guzzle/promises/PromisorInterface.php';	
		require 'guzzle/promises/TaskQueueInterface.php';	
		require 'guzzle/promises/TaskQueue.php';	
		require 'guzzle/promises/Utils.php';			
		require 'guzzle/promises/Is.php';
		require 'guzzle/promises/Promise.php';		
		require 'guzzle/promises/FulfilledPromise.php';		
		require 'guzzle/promises/RejectedPromise.php';
		require 'guzzle/promises/Create.php';	
		
		//require 'guzzle/psr/ClientInterface.php';		
		require 'guzzle/psr7/MessageTrait.php';
		//require 'guzzle/psr7/RequestInterface.php';
		//require 'guzzle/psr7/UriInterface.php';				
		require 'guzzle/psr7/Uri.php';	
		require 'guzzle/psr7/Stream.php';		
		require 'guzzle/psr7/Utils.php';		
		require 'guzzle/psr7/Request.php';
		require 'guzzle/psr7/Response.php';
		require 'guzzle/psr7/Message.php';

		require 'guzzle/Exception/GuzzleException.php';
		require 'guzzle/Exception/TransferException.php';
		require 'guzzle/Exception/RequestException.php';
		require 'guzzle/Exception/BadResponseException.php';
		require 'guzzle/Exception/ClientException.php';
		

			

		require 'guzzle/ClientInterface.php';	
		require 'guzzle/ClientTrait.php';
		require 'guzzle/BodySummarizerInterface.php';		
		require 'guzzle/BodySummarizer.php';		
		require 'guzzle/HandlerStack.php';	
		require 'guzzle/Handler/HeaderProcessor.php';	
				
		require 'guzzle/Handler/Proxy.php';
		require 'guzzle/Handler/StreamHandler.php';			
		require 'guzzle/Handler/CurlHandler.php';	
		require 'guzzle/Handler/CurlMultiHandler.php';	
		require 'guzzle/Handler/MockHandler.php';	
		require 'guzzle/Handler/EasyHandle.php';		
		require 'guzzle/Handler/CurlFactoryInterface.php';		
		require 'guzzle/Handler/CurlFactory.php';	
		require 'guzzle/RedirectMiddleware.php';
		require 'guzzle/PrepareBodyMiddleware.php';		
		require 'guzzle/Middleware.php';	
		//require 'guzzle/Pool.php';			
		require 'guzzle/Utils.php';		
		require 'guzzle/RequestOptions.php';
		require 'guzzle/Client.php';	

		require 'Tool/ArrayAccessorTrait.php';
		require 'Tool/GuardedPropertyTrait.php';		
		require 'Tool/BearerAuthorizationTrait.php';
		require 'Tool/MacAuthorizationTrait.php';		
		require 'Tool/ProviderRedirectTrait.php';	
		require 'Tool/QueryBuilderTrait.php';	
		require 'Tool/RequestFactory.php';	
		require 'Tool/RequiredParameterTrait.php';
		
		require 'Grant/AbstractGrant.php';	
		require 'Grant/AuthorizationCode.php';	
		require 'Grant/ClientCredentials.php';	
		require 'Grant/GrantFactory.php';	
		require 'Grant/Password.php';	
		require 'Grant/RefreshToken.php';	
		require 'Grant/Exception/InvalidGrantException.php';			

		require 'Provider/Exception/IdentityProviderException.php';			
		require 'Provider/AbstractProvider.php';
		require 'Provider/GenericProvider.php';
		
		require 'OptionProvider/OptionProviderInterface.php';
		require 'OptionProvider/PostAuthOptionProvider.php';
		require 'OptionProvider/HttpBasicAuthOptionProvider.php';
	
		require 'Token/AccessTokenInterface.php';
		require 'Token/ResourceOwnerAccessTokenInterface.php';
		require 'Token/AccessToken.php';

		
?>