variable "table_name" {
  description = "Name of the DynamoDB table for contact form submissions"
  type        = string
  default     = "contact-submissions"
}

variable "tags" {
  description = "Tags to apply to resources"
  type        = map(string)
  default     = {}
}

variable "project_name" {
  description = "Project name"
}

variable "environment" {
  description = "Ambiente de despliegue"
  type        = string
}