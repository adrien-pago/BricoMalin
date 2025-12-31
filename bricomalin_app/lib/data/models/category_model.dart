class CategoryModel {
  final int id;
  final String name;
  final String key;

  CategoryModel({
    required this.id,
    required this.name,
    required this.key,
  });

  factory CategoryModel.fromJson(Map<String, dynamic> json) {
    return CategoryModel(
      id: json['id'] as int,
      name: json['name'] as String,
      key: json['key'] as String,
    );
  }
}

