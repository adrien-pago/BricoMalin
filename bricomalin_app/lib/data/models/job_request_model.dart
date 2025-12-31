class JobRequestModel {
  final int id;
  final String title;
  final String description;
  final String department;
  final String? city;
  final bool isFree;
  final String? suggestedPrice;
  final String status;
  final DateTime? createdAt;
  final CategoryModel category;
  final RequesterModel? requester;

  JobRequestModel({
    required this.id,
    required this.title,
    required this.description,
    required this.department,
    this.city,
    required this.isFree,
    this.suggestedPrice,
    required this.status,
    this.createdAt,
    required this.category,
    this.requester,
  });

  factory JobRequestModel.fromJson(Map<String, dynamic> json) {
    return JobRequestModel(
      id: json['id'] as int,
      title: json['title'] as String,
      description: json['description'] as String,
      department: json['department'] as String,
      city: json['city'] as String?,
      isFree: json['isFree'] as bool? ?? false,
      suggestedPrice: json['suggestedPrice'] as String?,
      status: json['status'] as String,
      createdAt: json['createdAt'] != null
          ? DateTime.parse(json['createdAt'] as String)
          : null,
      category: CategoryModel.fromJson(json['category'] as Map<String, dynamic>),
      requester: json['requester'] != null
          ? RequesterModel.fromJson(json['requester'] as Map<String, dynamic>)
          : null,
    );
  }

  String get categoryName => category.name;
}

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

class RequesterModel {
  final int id;
  final String? displayName;
  final bool isPro;

  RequesterModel({
    required this.id,
    this.displayName,
    required this.isPro,
  });

  factory RequesterModel.fromJson(Map<String, dynamic> json) {
    return RequesterModel(
      id: json['id'] as int,
      displayName: json['displayName'] as String?,
      isPro: json['isPro'] as bool? ?? false,
    );
  }
}

