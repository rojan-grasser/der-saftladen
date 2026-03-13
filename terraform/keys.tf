resource "minio_iam_user" "s3_api_user" {
    name          = "s3-api-user"
    force_destroy = true
}

resource "minio_iam_policy" "s3_write_policy" {
    name   = "s3-write-policy"
    policy = jsonencode({
        Version = "2012-10-17"
        Statement = [
            {
                Effect   = "Allow"
                Action   = ["s3:PutObject", "s3:DeleteObject", "s3:GetObject"]
                Resource = ["arn:aws:s3:::forum/*", "arn:aws:s3:::users/*"]
            },
            {
                Effect   = "Allow"
                Action   = ["s3:ListBucket"]
                Resource = ["arn:aws:s3:::forum", "arn:aws:s3:::users"]
            }
        ]
    })
}

resource "minio_iam_user_policy_attachment" "s3_api_user_policy" {
    user_name   = minio_iam_user.s3_api_user.name
    policy_name = minio_iam_policy.s3_write_policy.name
}

resource "minio_iam_service_account" "s3_api_keys" {
    target_user = minio_iam_user.s3_api_user.name
}

output "s3_api_access_key" {
    value     = minio_iam_service_account.s3_api_keys.access_key
    sensitive = true
}

output "s3_api_secret_key" {
    value     = minio_iam_service_account.s3_api_keys.secret_key
    sensitive = true
}
