variable "project_name" {
  description = "Nombre del proyecto"
  type        = string
}

variable "environment" {
  description = "Ambiente"
  type        = string
}

variable "lambda_auth_invoke_arn" {
  description = "ARN de invocación de la lambda de autenticación"
  type        = string
}

variable "lambda_advise_invoke_arn" {
  description = "ARN de invocación de la lambda de advise"
  type        = string
}

variable "lambda_auth_function_name" {
  description = "Nombre de la función Lambda de autenticación"
  type        = string
}

variable "lambda_advise_function_name" {
  description = "Nombre de la función Lambda de advise"
  type        = string
}