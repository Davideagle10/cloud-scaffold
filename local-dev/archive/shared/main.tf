resource "aws_sns_topic" "main" {
  name = "${var.project_name}-topic"
}

resource "aws_sns_topic_subscription" "email" {
  topic_arn = aws_sns_topic.main.arn
  protocol  = "email"
  endpoint  = var.notification_email
}

resource "aws_sns_topic_subscription" "lambda_shared" {
  topic_arn = aws_sns_topic.main.arn
  protocol  = "lambda"
  endpoint  = aws_lambda_function.shared.arn
  depends_on = [aws_lambda_permission.allow_sns]
}

resource "aws_lambda_permission" "allow_sns" {
  statement_id  = "AllowExecutionFromSNS"
  action        = "lambda:InvokeFunction"
  function_name = aws_lambda_function.shared.function_name
  principal     = "sns.amazonaws.com"
  source_arn    = aws_sns_topic.main.arn
}