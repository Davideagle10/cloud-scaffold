output "admins_table_name" {
  value = aws_dynamodb_table.admins.name
}

output "admins_table_arn" {
  value = aws_dynamodb_table.admins.arn
}

output "requests_table_name" {
  value = aws_dynamodb_table.requests.name
}

output "requests_table_arn" {
  value = aws_dynamodb_table.requests.arn
}

output "data_corrections_table_name" {
  value = aws_dynamodb_table.data_corrections.name
}

output "data_corrections_table_arn" {
  value = aws_dynamodb_table.data_corrections.arn
}