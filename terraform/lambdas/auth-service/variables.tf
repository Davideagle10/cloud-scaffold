variable "project_name" {
  description = "The name of the project, used to prefix resources"
  type        = string
  default     = "Cloud-Scaffold"
}

variable "dynamodb_admins_table" {
  description = "The name of the DynamoDB table for admins"
  type        = string
  default     = "temp-admins-table"
}

variable "dynamodb_admins_table_arn" {
  description = "The ARN of the DynamoDB table for admins"
  type        = string
  default     = "arn:aws:dynamodb:us-east-1:000000000000:table/temp-admins-table"
}

variable "jwt_secret" {
  description = "The JWT secret for authentication"
  type        = string
  sensitive   = true
  default     = "dev-jwt-secret-temporary-value"
}

variable "account_id" {
  description = "The AWS account ID"
  type        = string
  default = "0000000"
}

variable "bref_php_layer" {
  description = "The ARN of the Bref PHP layer"
  type        = string
  default     = "arn:aws:lambda:us-east-1:534081306603:layer:php-81-fpm:59"
}

variable "aws_secret_access_key_dynamo_user" {
  description = "The AWS secret access key for the DynamoDB user"
  type        = string
  default     = "dummy-value-for-local-development"
}

variable "aws_access_key_dynamo_user" {
  description = "The AWS access key for the DynamoDB user"
  type        = string
  default     = "dummy-value-for-local-development"
}

variable "region" {
  description = "The AWS region"
  type        = string
  default     = "us-east-1"
}