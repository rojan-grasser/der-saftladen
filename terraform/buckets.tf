resource "minio_s3_bucket" "forum" {
    bucket = "forum"
}

resource "minio_s3_bucket_policy" "forum" {
    bucket = minio_s3_bucket.forum.bucket
    policy = jsonencode({
        Version = "2012-10-17"
        Statement = [
            {
                Sid       = "PublicRead"
                Effect    = "Allow"
                Principal = "*"
                Action    = ["s3:GetObject"]
                Resource  = ["arn:aws:s3:::forum/*"]
            }
        ]
    })
}

resource "minio_s3_bucket" "users" {
    bucket = "users"
}

resource "minio_s3_bucket_policy" "users" {
    bucket = minio_s3_bucket.users.bucket
    policy = jsonencode({
        Version = "2012-10-17"
        Statement = [
            {
                Sid       = "PublicRead"
                Effect    = "Allow"
                Principal = "*"
                Action    = ["s3:GetObject"]
                Resource  = ["arn:aws:s3:::users/*"]
            }
        ]
    })
}

