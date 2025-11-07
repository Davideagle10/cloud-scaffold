variable "project_name" {
  description = "The name of the project, used to prefix resources"
  type        = string
  default     = "Cloud-Scaffold"
}

variable "domain_name" {
  description = "The domain name for the CloudFront distribution"
  type        = string
  default     = ""
}

variable "jwt_secret" {
  description = "The JWT secret for authentication"
  type        = string
  sensitive   = true
  default     = "ev-jwt-secret-temporary-value"
}

variable "region" {
  description = "The AWS region"
  type        = string
  default     = "us-east-1"
}

variable "account_id" {
  description = "The AWS account ID"
  type        = string
  default     = "0000000"
}

variable "bref_php_layer" {
  description = "The ARN of the Bref PHP 8.1 FPM layer"
  type        = string
  default     = "arn:aws:lambda:us-east-1:534081306603:layer:php-81-fpm:111"
}

variable "environment" {
  description = "Ambiente de despliegue"
  type        = string
  default     = "dev"
}

variable "notification_email" {
  description = "Email for SNS notifications (optional)"
  type        = string
  default     = "dev@example.com"
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