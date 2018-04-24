<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2018031702393796",

		//商户私钥
		'merchant_private_key' => "MIIEogIBAAKCAQEAxoE6isD1p6F7OWIPU3WY3UPbbjx+0lj4WcfTrQiE3NYmhCs9PFhs+oIisc8nVPVGpgapQz9A2lEDPeAZA0ZgR01LzMM1rY6yCniJ61paZXBrltMuXY+P+tp1AUizsREbIvB2fWpefwuW2WB+3orwxoHy2/Xsc1gDC71cAVhs2ZcfS1LRbHMkrIaihai+imNw8rPFClEWOGb4jGX6PaJMdxjBTGgOaKUY/yLvOEzgsBHEz+teV6vx/J45PpVwmhzzz5j3NnPjlpvsMsAFliAsaaQZQQmiLtO5IUvsrjCUqmc7LSxAp5gWTwdVr6eDlYJzikHdRhxyM11ABtjHhYO+2wIDAQABAoIBABkIqSJgjVpzof+tnzMuTHXzHdQ8Kt5KPB/OdKKIxYC4PSlD7rgSisPY75ku/83WFO/RPT1u17Xwq5D9FwpzsKatV9bd84IM0eVCKFqXDXlngRoHTfyco1LGhCUCTy8QcjSo0LimVn59LqwBYF6edRkDwGX/EK/HNLMhUV4WEbwq+pIwHCeIk36Uac8lnz8VZEEqwEak1IFKwRKkrEmaKucs4oO4jkoEWeH0NsMOf7PeOedOmky8FtCtr9x4zoY/2WNoTrTyvJLJ+U6xzoSktq1FTYBZ8Kj9SUeFq7C++eYSPpmWLKC1Rvrhfo2yZuDMviL0UuiDrvl56r2WaeNfoSkCgYEA/hN1Z/+qeFneJK2xQ3VDRCGZ/el1EQpHLyCmX518OpVotu7IPbd1fRPsaDuXqU31i0OTeSzTu8RBmL+MjFfdeTIpDgp4kct9KtHIxjqFU8kG0EIwRWv3cxZwpiGFsWoGrz0zZoQalCUs/sKFFRFj6+U7UsjB8SI38AEErvX4rZcCgYEAyAIKv0PQSrgR4k5OEmZJ6uMI0kRKWE+Bj3TM/KZUVE9ncnNVuaE27An/hrsgvAiRENT2Lzc43VT/SR0a8xf0936A6Xk/ghDLbrBBdF8RGE92kyCBDh7S6UASR190sbj8Q1bvxSRUWor4/YeKjGZVgrqpf9bxZPAJtULXrJeMqV0CgYBcSrk1I9vKh+p3rFhdxrS+hO4pG6qYnAErj1KgHNbJeQhmmupF8OROoPQZELyQlW8CPXPOaognBq1FTR9Cw5ABvETJgug1HZPEABxMVWmk+7rr338lWIi0V4xn7Hlh7r+q3DJVpNr6wV5M1F//PSfswoIAGkjIzMNA/T20SEki1QKBgAdd++NbrhpyeUYquDl5RxO91R9ZoJtsIkuUc9CCl+ybGCZhFTFHPOyo2Z6u2d/TOPSzw3WtxQxyCw04Yzu9ECrln/35l1tFHxkuqNqOfeS1WJrGwboxbHdnAsVaQMQjGjeoxiAu3GSRZeaAzZuqUVAFbCTnV/Dt3o2srqEO3AGJAoGAfFSB1GJEfUiOyJK/yEVaNEwOGmk2AJRZczqp/eEzK1kZXnTYra1nZonev6xsUGQ56Bqyxs4FaLiYcHNFxgePFRmyIvj5M/vup/TO+kzVUHtWtuYOBNLj2L9F5xTUgQu9yXkeQNamwLecEGUBdX9s+h0aR9ol6bNHTUppYygwp/k=",
		
		//异步通知地址
		'notify_url' => "http://godwu2846.gicp.net:88/alipay/notify_url.php",
		
		//同步跳转
		'return_url' => "http://godwu2846.gicp.net:88/alipay/return_url.php",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",
//		'gatewayUrl' => "https://openapi.alipaydev.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAxQcNGPnYpd2TS2jRZjE1d9rp1qlYu/Urf7vu6l3iY5QDAOMBPfY8SY5fJw5XGLiUfdkSLRPLGZ2CHzM2C+rtRpGLpX8N6Kl2qqTN4QZtAvipsknGUydHwJj4d5vhiKT+Y4gAGDv5Xr/eN7Etcc+37KFbmfFEf88nNVGg7MmtWO0Oapuoz1j5hHDtC2pz6SHsdik60pkxSP89LdqxpXATNOpRaaI3fVr+14Jsa45MeBtysiRxqknVVVaI5CIDoQKjs98NPxO+d89S4ON57p/mGy3g/BrVzJuQ1XCDDqdqoAHIHYfjkPlm7BQkkjcnW/Hg6MMP5CJ03vIV292BlhsEBwIDAQAB",
);