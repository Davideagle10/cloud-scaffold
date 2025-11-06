output "api_gateway_url" {
  value       = aws_apigatewayv2_stage.main.invoke_url
  description = "API Gateway v2 invoke URL"
}

output "api_gateway_id" {
  value       = aws_apigatewayv2_api.main.id
  description = "API Gateway v2 ID"
}

output "api_gateway_endpoint" {
  value       = aws_apigatewayv2_api.main.api_endpoint
  description = "API Gateway v2 endpoint"
}
