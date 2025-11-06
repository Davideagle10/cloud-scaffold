module "frontend" {
  source       = "./frontend"
  project_name = var.project_name
  domain_name  = var.domain_name
}

module "dynamodb" {
  source       = "./dynamodb"
  project_name = var.project_name
  environment  = var.environment
}

module "sns" {
  source             = "./sns"
  project_name       = var.project_name
  notification_email = var.notification_email
}

# Módulo para Lambda de Auth
module "lambda_auth" {
  source       = "./lambdas/auth-service"
  project_name = var.project_name

  dynamodb_admins_table      = module.dynamodb.admins_table_name
  dynamodb_admins_table_arn  = module.dynamodb.admins_table_arn
  jwt_secret                 = var.jwt_secret
  account_id                 = var.account_id
  bref_php_layer             = var.bref_php_layer
  aws_secret_access_key_dynamo_user = var.aws_secret_access_key_dynamo_user
  aws_access_key_dynamo_user        = var.aws_access_key_dynamo_user
}

module "lambda_advise" {
  source       = "./lambdas/advise-service"
  project_name = var.project_name

  dynamodb_requests_table      = module.dynamodb.requests_table_name
  dynamodb_requests_table_arn  = module.dynamodb.requests_table_arn
  jwt_secret                   = var.jwt_secret
  account_id                   = var.account_id
  bref_php_layer               = var.bref_php_layer
  sns_topic_arn                = module.sns.sns_topic_arn
  aws_secret_access_key_dynamo_user = var.aws_secret_access_key_dynamo_user
  aws_access_key_dynamo_user        = var.aws_access_key_dynamo_user
  }

module "api_gateway" {
  source       = "./api-gateway"
  project_name = var.project_name
  environment  = var.environment

  # las siguientes líneas construyen la URI completa requerida por API Gateway
  # API Gateway expects the integration URI in the form:
  # arn:aws:apigateway:${region}:lambda:path/2015-03-31/functions/${lambda_arn}/invocations
  # The api-gateway module will receive the full URI via these two variables.
  lambda_auth_invoke_arn = module.lambda_auth.invoke_arn
  lambda_advise_invoke_arn = module.lambda_advise.invoke_arn
  lambda_auth_function_name   = module.lambda_auth.function_name
  lambda_advise_function_name = module.lambda_advise.function_name
}

output "frontend_url" {
  value = module.frontend.cloudfront_domain
}

output "api_gateway_url" {
  value = module.api_gateway.api_gateway_url
}

output "dynamodb_tables" {
  value = {
    admins           = module.dynamodb.admins_table_name
    requests         = module.dynamodb.requests_table_name
    data_corrections = module.dynamodb.data_corrections_table_name
  }
}

output "sns_topic_arn" {
  value = module.sns.sns_topic_arn
}
