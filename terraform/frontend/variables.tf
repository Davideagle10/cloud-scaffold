variable "project_name" {
  description = "The name of the project, used to prefix resources"
  type        = string
}

variable "domain_name" {
  description = "The domain name for the CloudFront distribution"
  type        = string
  default     = ""
}