output "function_name" {
  value = aws_lambda_function.advise_service.function_name
}

output "invoke_arn" {
  value = aws_lambda_function.advise_service.invoke_arn
}

output "role_arn" {
  value = aws_iam_role.lambda_exec.arn
}