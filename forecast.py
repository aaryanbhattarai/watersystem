"""
Generate demand forecasts using trained models,
save results to forecast_result.json,
and insert forecast data into MySQL database table 'forecasts'.
"""

import pandas as pd
import numpy as np
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score
import joblib
import json
import os
import sys
from datetime import datetime
import mysql.connector
from mysql.connector import Error

# --- 1. Configuration ---
USER_UPLOAD_PATH = 'user_upload1.csv'  # Input CSV file - internal only, no saving/export
MODEL_DIR = '.'  # Models folder
OUTPUT_JSON_PATH = 'forecast_result.json'  # JSON output path

# MySQL DB credentials - update with your actual credentials
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'inventory_db'
}

REQUIRED_COLUMNS = [
    'Product_ID', 'Product_Name', 'Category', 'Monthly_Sales',
    'Time', 'Price ($)', 'Stock_Level', 'Promotion'
]

# --- 2. Load Models and Encoders ---
def load_models_and_encoders(model_dir):
    print("[INFO] Loading trained models and encoders...")
    try:
        models = {
            'Linear Regression': joblib.load(os.path.join(model_dir, 'linear_regression_model.pkl')),
            'Random Forest': joblib.load(os.path.join(model_dir, 'random_forest_model.pkl'))
        }
        encoders = {
            'le_category': joblib.load(os.path.join(model_dir, 'le_category.pkl')),
            'le_promotion': joblib.load(os.path.join(model_dir, 'le_promotion.pkl'))
        }
        print("[INFO] Models and encoders loaded successfully.")
        return models, encoders
    except Exception as e:
        print(f"[ERROR] Loading models/encoders failed: {e}", file=sys.stderr)
        sys.exit(1)

# --- 3. Load and preprocess user data ---
def load_and_preprocess_user_data(filepath, encoders):
    print(f"[INFO] Loading user data from '{filepath}'...")
    if not os.path.exists(filepath):
        print(f"[ERROR] User data file '{filepath}' not found.", file=sys.stderr)
        sys.exit(1)
    try:
        df = pd.read_csv(filepath)
        print(f"[INFO] User data loaded. Shape: {df.shape}")
    except Exception as e:
        print(f"[ERROR] Reading user CSV failed: {e}", file=sys.stderr)
        sys.exit(1)

    missing_cols = set(REQUIRED_COLUMNS) - set(df.columns)
    if missing_cols:
        print(f"[ERROR] Missing columns: {missing_cols}", file=sys.stderr)
        sys.exit(1)

    original_data = df.copy()

    try:
        le_category = encoders['le_category']
        le_promotion = encoders['le_promotion']

        def safe_transform(series, encoder, feature_name):
            transformed = []
            classes = set(encoder.classes_)
            for val in series:
                if val in classes:
                    transformed.append(encoder.transform([val])[0])
                else:
                    print(f"[WARNING] Unseen '{val}' in '{feature_name}', encoding as 0.")
                    transformed.append(0)
            return transformed

        df['Category_encoded'] = safe_transform(df['Category'], le_category, 'Category')
        df['Promotion_encoded'] = safe_transform(df['Promotion'], le_promotion, 'Promotion')

        df['Time'] = pd.to_datetime(df['Time'])
        df['Year'] = df['Time'].dt.year
        df['Month'] = df['Time'].dt.month

    except Exception as e:
        print(f"[ERROR] Preprocessing failed: {e}", file=sys.stderr)
        sys.exit(1)

    feature_columns = [
        'Product_ID', 'Category_encoded', 'Price ($)', 'Stock_Level', 'Promotion_encoded', 'Year', 'Month'
    ]

    missing_features = set(feature_columns) - set(df.columns)
    if missing_features:
        print(f"[ERROR] Missing features: {missing_features}", file=sys.stderr)
        sys.exit(1)

    X_user = df[feature_columns]
    y_user_actual = df['Monthly_Sales']

    print(f"[INFO] Features prepared. Shape: {X_user.shape}")
    return X_user, y_user_actual, original_data

# --- 4. Insert forecasts into DB ---
def insert_forecasts_into_db(forecast_records):
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()

        insert_query = """
            INSERT INTO forecasts
            (user_id, product_id, product_name, category, monthly_sales, time_period, price, stock_level,
             promotion, predicted_demand, model_used, mae, rmse, r2_score, created_at)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """

        USER_ID_PLACEHOLDER = 0  # Update if you want to store user ID dynamically

        data_to_insert = []
        for rec in forecast_records:
            data_to_insert.append((
                USER_ID_PLACEHOLDER,
                rec["Product_ID"],
                rec["Product_Name"],
                rec["Category"],
                rec["Input_Monthly_Sales"],
                rec["Forecasted_Time"],
                rec["Price ($)"],
                rec["Stock_Level"],
                rec["Promotion"],
                rec["Predicted_Demand"],
                rec["Model_Used"],
                rec["MAE"],
                rec["RMSE"],
                rec["R2"],
                datetime.now()
            ))

        cursor.executemany(insert_query, data_to_insert)
        conn.commit()
        print(f"[INFO] Inserted {cursor.rowcount} forecast records into DB.")
    except Error as e:
        print(f"[ERROR] DB insertion failed: {e}", file=sys.stderr)
    finally:
        if cursor:
            cursor.close()
        if conn and conn.is_connected():
            conn.close()

# --- 5. Suggestion logic ---
def get_suggestion(predicted_demand, stock_level, promotion):
    promo = promotion.lower()
    if predicted_demand > stock_level:
        return "Increase stock to meet demand"
    elif predicted_demand < 0.5 * stock_level:
        if promo == 'no':
            return "Consider promotions to boost sales"
        else:
            return "Review promotion effectiveness"
    else:
        return "Stock level adequate"

# --- 6. Generate forecasts ---
def generate_forecasts(models, X_user, y_user_actual, original_data):
    print("\n[INFO] Generating forecasts using trained models...")
    all_forecasts = {}

    for name, model in models.items():
        print(f"\n--- Forecasts with {name} ---")
        try:
            y_pred = model.predict(X_user)

            mae = mean_absolute_error(y_user_actual, y_pred)
            rmse = np.sqrt(mean_squared_error(y_user_actual, y_pred))
            r2 = r2_score(y_user_actual, y_pred)

            print(f"[RESULT] {name} Metrics:")
            print(f"  MAE: {mae:.4f}, RMSE: {rmse:.4f}, R2: {r2:.4f}")

            results_list = []
            for i in range(len(original_data)):
                row = original_data.iloc[i].to_dict()
                input_time_obj = pd.to_datetime(row['Time'], format='%Y-%m')
                if input_time_obj.month == 12:
                    forecast_year = input_time_obj.year + 1
                    forecast_month = 1
                else:
                    forecast_year = input_time_obj.year
                    forecast_month = input_time_obj.month + 1
                forecast_time_str = f"{forecast_year}-{forecast_month:02d}"

                pred_demand = int(y_pred[i])
                suggestion = get_suggestion(pred_demand, int(row['Stock_Level']), row['Promotion'])

                results_list.append({
                    "Product_ID": int(row['Product_ID']),
                    "Product_Name": row['Product_Name'],
                    "Category": row['Category'],
                    "Input_Monthly_Sales": int(row['Monthly_Sales']),
                    "Input_Time": row['Time'],
                    "Forecasted_Time": forecast_time_str,
                    "Price ($)": float(row['Price ($)']),
                    "Stock_Level": int(row['Stock_Level']),
                    "Promotion": row['Promotion'],
                    "Predicted_Demand": pred_demand,
                    "Suggestion": suggestion,
                    "Model_Used": name,
                    "MAE": float(mae),
                    "RMSE": float(rmse),
                    "R2": float(r2)
                })

            all_forecasts[name] = results_list

            # Insert into MySQL DB
            insert_forecasts_into_db(results_list)

        except Exception as e:
            print(f"[ERROR] Forecast generation failed for {name}: {e}", file=sys.stderr)
            continue

    if not all_forecasts:
        print("[ERROR] No forecasts generated.", file=sys.stderr)
        sys.exit(1)

    return all_forecasts

# --- 7. Save results to JSON ---
def save_forecast_results(all_forecasts, output_path):
    print(f"\n[INFO] Saving forecast results to '{output_path}'...")
    try:
        output_data = {
            "generated_at": datetime.now().isoformat(),
            "forecasts": all_forecasts
        }
        with open(output_path, 'w') as f:
            json.dump(output_data, f, indent=4)
        print("[INFO] Forecast results saved successfully.")
    except Exception as e:
        print(f"[ERROR] Saving forecast results failed: {e}", file=sys.stderr)
        sys.exit(1)

# --- 8. Main ---
def main():
    print("--- Inventory Demand Forecasting - Generate Forecasts ---")
    models, encoders = load_models_and_encoders(MODEL_DIR)
    X_user, y_user_actual, original_data = load_and_preprocess_user_data(USER_UPLOAD_PATH, encoders)
    all_forecasts = generate_forecasts(models, X_user, y_user_actual, original_data)
    save_forecast_results(all_forecasts, OUTPUT_JSON_PATH)
    print("\n[INFO] Forecasting process completed.")
    print(f"[INFO] Results available in '{OUTPUT_JSON_PATH}'.")

if __name__ == "__main__":
    main()
