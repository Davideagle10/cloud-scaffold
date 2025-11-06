# Tabla para administradores: auth
resource "aws_dynamodb_table" "admins" {
  name         = "${var.project_name}-admins"
  billing_mode = "PAY_PER_REQUEST"
  hash_key     = "email"
  range_key    = "password"

  attribute {
    name = "email"
    type = "S"
  }

  attribute {
    name = "password"
    type = "S"
  }

  tags = {
    Project     = var.project_name
    Environment = var.environment
  }
}

# Tabla para solicitudes: advise-service
resource "aws_dynamodb_table" "requests" {
  name         = "${var.project_name}-requests"
  billing_mode = "PAY_PER_REQUEST"
  hash_key     = "email"
  range_key    = "created_at"

  attribute {
    name = "email"
    type = "S"
  }

  attribute {
    name = "created_at"
    type = "N"
  }

  global_secondary_index {
    name            = "EmailIndex"
    hash_key        = "email"
    projection_type = "ALL"
    read_capacity   = 5
    write_capacity  = 5
  }

  global_secondary_index {
    name            = "CreatedAtIndex"
    hash_key        = "created_at"
    projection_type = "ALL"
    read_capacity   = 5
    write_capacity  = 5
  }

  tags = {
    Project     = var.project_name
    Environment = var.environment
  }
}

# Tabla para correcciones de datos
resource "aws_dynamodb_table" "data_corrections" {
  name         = "${var.project_name}-data-corrections"
  billing_mode = "PAY_PER_REQUEST"
  hash_key     = "id"

  attribute {
    name = "id"
    type = "S"
  }

  tags = {
    Project     = var.project_name
    Environment = var.environment
  }
}
