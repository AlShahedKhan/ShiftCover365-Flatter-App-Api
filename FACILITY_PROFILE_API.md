# Facility Profile API for Manager Role

## Overview
The Facility Profile API allows managers to view and update their facility profile information. This API is restricted to users with the 'manager' role only.

## Authentication
All endpoints require a valid JWT token in the Authorization header:
```
Authorization: Bearer {your_jwt_token}
```

## Endpoints

### 1. Get Facility Profile
**GET** `/api/facility-profile`

Retrieves the current manager's facility profile information.

**Response:**
```json
{
    "success": true,
    "status_code": 200,
    "message": "Facility profile retrieved successfully",
    "profile": {
        "personal_details": {
            "name": "John Doe",
            "user_id": 1,
            "designation": "Senior Manager"
        },
        "job_details": {
            "role": "manager",
            "experience": "5 years",
            "case_types": "Criminal, Civil"
        },
        "administrative_info": {
            "joining_date": "2020-01-15",
            "work_location": "Main Office",
            "employment_type": "Full-time"
        }
    }
}
```

### 2. Update Facility Profile
**PUT** `/api/facility-profile`

Updates the manager's facility profile information.

**Request Body:**
```json
{
    "personal_details": {
        "name": "John Doe",
        "designation": "Senior Manager"
    },
    "job_details": {
        "experience": "5 years",
        "case_types": "Criminal, Civil"
    },
    "administrative_info": {
        "joining_date": "2020-01-15",
        "work_location": "Main Office",
        "employment_type": "Full-time"
    }
}
```

**Response:**
```json
{
    "success": true,
    "status_code": 200,
    "message": "Facility profile updated successfully",
    "profile": {
        "personal_details": {
            "name": "John Doe",
            "user_id": 1,
            "designation": "Senior Manager"
        },
        "job_details": {
            "role": "manager",
            "experience": "5 years",
            "case_types": "Criminal, Civil"
        },
        "administrative_info": {
            "joining_date": "2020-01-15",
            "work_location": "Main Office",
            "employment_type": "Full-time"
        }
    }
}
```

## Field Descriptions

### Personal Details
- **name**: Full name of the manager
- **user_id**: Unique user identifier (read-only)
- **designation**: Job title or designation

### Job Details
- **role**: User role (always "manager" for this API)
- **experience**: Years of experience or experience description
- **case_types**: Types of cases the manager handles

### Administrative Info
- **joining_date**: Date when the manager joined (YYYY-MM-DD format)
- **work_location**: Physical work location
- **employment_type**: Type of employment (e.g., "Full-time", "Part-time", "Contract")

## Validation Rules

### Update Profile
- `personal_details.name`: Optional, string, max 255 characters
- `personal_details.designation`: Optional, string, max 255 characters
- `job_details.experience`: Optional, string, max 255 characters
- `job_details.case_types`: Optional, string, max 500 characters
- `administrative_info.joining_date`: Optional, valid date format
- `administrative_info.work_location`: Optional, string, max 255 characters
- `administrative_info.employment_type`: Optional, string, max 100 characters

## Error Responses

### 403 Forbidden
```json
{
    "success": false,
    "status_code": 403,
    "message": "Only managers can access facility profile"
}
```

### 401 Unauthorized
```json
{
    "success": false,
    "status_code": 401,
    "message": "Forbidden: You are not authorized to perform this action."
}
```

### 422 Validation Error
```json
{
    "success": false,
    "status_code": 422,
    "message": "Validation failed",
    "errors": {
        "personal_details.name": ["The name field must be a string."],
        "administrative_info.joining_date": ["The joining date field must be a valid date."]
    }
}
```

## Usage Examples

### cURL Example - Get Profile
```bash
curl -X GET "http://your-domain.com/api/facility-profile" \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Content-Type: application/json"
```

### cURL Example - Update Profile
```bash
curl -X PUT "http://your-domain.com/api/facility-profile" \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Content-Type: application/json" \
  -d '{
    "personal_details": {
        "name": "John Doe",
        "designation": "Senior Manager"
    },
    "job_details": {
        "experience": "5 years",
        "case_types": "Criminal, Civil"
    },
    "administrative_info": {
        "joining_date": "2020-01-15",
        "work_location": "Main Office",
        "employment_type": "Full-time"
    }
}'
```

## Notes
- Only users with 'manager' role can access these endpoints
- All fields are optional during updates
- The API maintains the existing role and user_id values
- Dates should be in YYYY-MM-DD format
- Empty or null values will be stored as empty strings in the database 
