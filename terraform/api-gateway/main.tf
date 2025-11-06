# API Gateway v2 (HTTP API)
resource "aws_apigatewayv2_api" "main" {
  name          = "${var.project_name}-api"
  protocol_type = "HTTP"
}

# Lambda Integration para Auth Service
resource "aws_apigatewayv2_integration" "auth" {
  api_id           = aws_apigatewayv2_api.main.id
  integration_type = "AWS_PROXY"
  integration_uri  = var.lambda_auth_invoke_arn

  payload_format_version = "2.0"
}

# Lambda Integration para Advise Service
resource "aws_apigatewayv2_integration" "advise" {
  api_id           = aws_apigatewayv2_api.main.id
  integration_type = "AWS_PROXY"
  integration_uri  = var.lambda_advise_invoke_arn

  payload_format_version = "2.0"
}

# Routes para Auth Service
resource "aws_apigatewayv2_route" "auth" {
  api_id    = aws_apigatewayv2_api.main.id
  route_key = "ANY /auth/{proxy+}"
  target    = "integrations/${aws_apigatewayv2_integration.auth.id}"
}

resource "aws_apigatewayv2_route" "auth_root" {
  api_id    = aws_apigatewayv2_api.main.id
  route_key = "ANY /auth"
  target    = "integrations/${aws_apigatewayv2_integration.auth.id}"
}

# Routes para Advise Service
resource "aws_apigatewayv2_route" "advise" {
  api_id    = aws_apigatewayv2_api.main.id
  route_key = "ANY /api/{proxy+}"
  target    = "integrations/${aws_apigatewayv2_integration.advise.id}"
}

resource "aws_apigatewayv2_route" "advise_root" {
  api_id    = aws_apigatewayv2_api.main.id
  route_key = "ANY /api"
  target    = "integrations/${aws_apigatewayv2_integration.advise.id}"
}

# Stage con auto-deploy
resource "aws_apigatewayv2_stage" "main" {
  api_id      = aws_apigatewayv2_api.main.id
  name        = var.environment
  auto_deploy = true

  access_log_settings {
    destination_arn = aws_cloudwatch_log_group.api_gateway.arn
    format = jsonencode({
      requestId      = "$context.requestId"
      ip             = "$context.identity.sourceIp"
      requestTime    = "$context.requestTime"
      httpMethod     = "$context.httpMethod"
      routeKey       = "$context.routeKey"
      status         = "$context.status"
      protocol       = "$context.protocol"
      responseLength = "$context.responseLength"
    })
  }
}

# CloudWatch Log Group para API Gateway
resource "aws_cloudwatch_log_group" "api_gateway" {
  name              = "/aws/apigateway/${var.project_name}-api"
  retention_in_days = 7
}

# Permisos para que API Gateway v2 invoque las Lambdas
resource "aws_lambda_permission" "auth" {
  statement_id  = "AllowExecutionFromAPIGatewayV2"
  action        = "lambda:InvokeFunction"
  function_name = var.lambda_auth_function_name
  principal     = "apigateway.amazonaws.com"
  source_arn    = "${aws_apigatewayv2_api.main.execution_arn}/*/*"
}

resource "aws_lambda_permission" "advise" {
  statement_id  = "AllowExecutionFromAPIGatewayV2"
  action        = "lambda:InvokeFunction"
  function_name = var.lambda_advise_function_name
  principal     = "apigateway.amazonaws.com"
  source_arn    = "${aws_apigatewayv2_api.main.execution_arn}/*/*"
}
