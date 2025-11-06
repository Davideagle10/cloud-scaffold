resource "aws_sns_topic" "main" {
  name = "${var.project_name}-topic"
}


resource "aws_sns_topic_subscription" "email_subscription" {
   topic_arn = aws_sns_topic.main.arn
   protocol  = "email"
  endpoint  = var.notification_email
 }

action "aws_sns_publish" "example" {
  config {
    topic_arn = aws_sns_topic.example.arn
    message   = "Hello from Terraform"
  }
}

resource "terraform_data" "example" {
  input = "trigger-message"

  lifecycle {
    action_trigger {
      events  = [before_create, before_update]
      actions = [action.aws_sns_publish.example]
    }
  }
}